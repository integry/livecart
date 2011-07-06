LiveCart home page: http://livecart.com
Installation guide: http://doc.livecart.com/help/installation

LiveCart links several libraries as Git submodules, so to pull their code run the following commands after cloning the LiveCart repository:
cd livecart # <-- the directory of the cloned repository
git submodule update --init --recursive
