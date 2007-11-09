<?php

ClassLoader::import('application.model.system.Language');
ClassLoader::import('application.model.Currency');
ClassLoader::import('application.model.product.Manufacturer');
ClassLoader::import('application.model.category.Category');
ClassLoader::import('application.model.product.Product');
ClassLoader::import('application.model.order.CustomerOrder');
ClassLoader::import('application.model.user.User');

class LiveCartImporter
{
    const MAX_RECORDS = 1;

    private $driver;

    public function __construct(LiveCartImportDriver $driver)
    {
        $this->driver = $driver;
        $this->reset();
    }

    /**
     *  Determine what kind of data can be imported
     */
    public function getItemTypes()
    {
        $supportedTypes = array();

        foreach ($this->getRecordTypes() as $type)
        {
            if (call_user_func_array(array($this->driver, 'is' . $type), array()))
            {
                $supportedTypes[] = $type;
            }
        }

        return $supportedTypes;
    }

    public function reset()
    {
        @unlink($this->getTypeFile());
        @unlink($this->getProgressFile());
        @unlink($this->getCountFile());
        @unlink($this->getOffsetsFile());
    }

    /**
     *  Get current data type
     */
    public function getCurrentType()
    {
        $file = $this->getTypeFile();

        if (!file_exists($file))
        {
            $this->setNextType();
        }

        return include $file;
    }

    /**
     *  Get total number of importable records of the current data type
     */
    public function getCurrentRecordCount()
    {
        $this->getCurrentType();

        return include $this->getCountFile();
    }

    /**
     *  Get the number of imported records of the current data type
     */
    public function getCurrentProgress()
    {
        $file = $this->getProgressFile();

        if (!file_exists($file))
        {
            $this->setProgress(0);
        }

        return include $file;
    }

    /**
     *  Processes data import - one type of data at a time
     *
     *  Data size is limited to 50 records per call
     */
    public function process()
    {
        $type = $this->getCurrentType();

        // import completed
        if (is_null($type))
        {
            return null;
        }

        $total = $this->getCurrentRecordCount();

        $offsets = $this->getIdOffsets();
        $offset = isset($offsets[$type]) ? $offsets[$type] : null;

        for ($k = 1; $k <= self::MAX_RECORDS; $k++)
        {
            $id = $this->getCurrentProgress();

            $record = call_user_func_array(array($this->driver, 'getNext' . $type), array($id));

            // import completed?
            if (null == $record)
            {
                $this->setNextType();
                break;
            }

            // apply ID offset
            if (is_numeric($record->getID()))
            {
                $record->setID($record->getID() + $offset);
            }

            try
            {
                $record->save();
            }
            catch (ARException $e)
            {

            }

            $this->setProgress($this->getCurrentProgress() + 1);
        }

        return array('type' => $type, 'progress' => $this->getCurrentProgress(), 'total' => $total);
    }

    private function getIdOffsets()
    {
        $file = $this->getOffsetsFile();

        if (!file_exists($file))
        {
            $offsets = array();

            $types = $this->getRecordTypes();
            unset($types[array_search('Language', $types)]);

            foreach ($types as $type)
            {
                $f = new ARSelectFilter();
                $f->setOrder(new ARFieldHandle($type, 'ID'), 'DESC');
                $f->setLimit(1);
                $record = array_shift(ActiveRecordModel::getRecordSetArray($type, $f));
                $offsets[$type] = $record['ID'] + 1;
            }

            file_put_contents($file, '<?php return ' . var_export($offsets, true) . '; ?>');
        }

        return include $file;
    }

    public function getRecordTypes()
    {
        return array(
                'Language',
                'Currency',
				'Manufacturer',
                'Category',
                'Product',
                'User',
                'CustomerOrder',
            );
    }

    private function setNextType()
    {
        @unlink($this->getProgressFile());

        $file = $this->getTypeFile();
        $types = $this->getItemTypes();

        if (!file_exists($file))
        {
            $typeIndex = -1;
        }
        else
        {
            $typeIndex = array_search(include $file, $types);
        }

        $typeIndex++;

        $type = isset($types[$typeIndex]) ? $types[$typeIndex] : null;

        // get total record count of this type
        if (!is_null($type))
        {
            $count = $this->driver->getTotalRecordCount($type);
            file_put_contents($this->getCountFile(), '<?php return ' . var_export($count, true) . '; ?>');
        }

        file_put_contents($file, '<?php return ' . var_export($type, true) . '; ?>');
    }

    private function setProgress($progress)
    {
        file_put_contents($this->getProgressFile(), '<?php return ' . var_export($progress, true) . '; ?>');
    }

    private function getOffsetsFile()
    {
        return ClassLoader::getRealPath('cache') . '/currentIdOffsets.php';
    }

    private function getTypeFile()
    {
        return ClassLoader::getRealPath('cache') . '/currentImportType.php';
    }

    private function getProgressFile()
    {
        return ClassLoader::getRealPath('cache') . '/currentImportProgress.php';
    }

    private function getCountFile()
    {
        return ClassLoader::getRealPath('cache') . '/currentImportCount.php';
    }
}

?>