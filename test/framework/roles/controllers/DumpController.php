<?php
/**
 * 
 * @role test
 */
class DumpController
{
	/**
	 * @role subtest
	 */
	public function test()
	{
		
	}
	
	public function noRole()
	{
		
	}
	
	/**
	 * @role !another.another
	 */
	public function test2()
	{
		
	}
	
	/**
	 * @role !another
	 */
	public function test3()
	{
		
	}
}
?>