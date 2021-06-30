[root user]
apt update
apt install -y sudo
apt install -y less
apt install -y vim

adduser frank
[password] 3TmViPoK
usermod -aG sudo frank

su frank
cd /application
composer global require "laravel/lumen-installer"

vi /etc/.profile
[:edit and close with :x]
export PATH="$PATH:/home/frank/.composer/vendor/bin/"


vi /hom/frank/.bashrc
export PATH="$PATH:/home/frank/.composer/vendor/bin/"

source ~/.bashrc

[:controll-Check]
which lumen

cd /application
lumen new lumenapp
mv ./lumenapp/* ./
mv lumenapp/.editorconfig ./
mv lumenapp/.env.example ./
mv lumenapp/.gitignore ./
mv lumenapp/.styleci.yml ./
