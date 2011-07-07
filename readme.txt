LiveCart home page: http://livecart.com
Installation guide: http://doc.livecart.com/help/installation

Cloning LiveCart repository
================

Master branch is used for the bleeding edge development. To clone the master branch, run the following command:
    git clone git://github.com/integry/livecart.git

1.4.0 (and other version branches in future) contains the _stable_ code. New features are rarely (if ever) added to them and only bug-fix commits are back-ported (_cherry-picked_) from the master or other branches. To clone, for example, the stable 1.4.0 branch, run the following command:
    git clone -b 1.4.0 git://github.com/integry/livecart.git

That's not all yet. LiveCart links several libraries as Git submodules, so to pull their code, also run the following commands after cloning the LiveCart repository:
    cd livecart # <-- the directory of the cloned repository
    git submodule update --init --recursive
