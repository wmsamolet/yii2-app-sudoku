Sudoku Game (based on Yii2 Advanced Project Template)
============
Sudoku Game based on Yii 2 Advanced Project Template [Yii 2](http://www.yiiframework.com/).
Documentation is at [docs/guide/README.md](docs/guide/README.md).

## Installing using Vagrant

This way is the easiest but long (~20 min).

**This installation way doesn't require pre-installed software (such as web-server, PHP, MySQL etc.)** - just do next steps!

#### Manual for Linux/Unix users

1. Install [VirtualBox](https://www.virtualbox.org/wiki/Downloads)
2. Install [Vagrant](https://www.vagrantup.com/downloads.html)
3. Create GitHub [personal API token](https://github.com/blog/1509-personal-api-tokens)
3. Prepare project:
   
   ```bash
   git clone git@github.com:wmsamolet/yii2-app-sudoku.git
   cd yii2-app-sudoku/vagrant/config
   cp vagrant-local.example.yml vagrant-local.yml
   ```
   
4. Place your GitHub personal API token to `vagrant-local.yml`
5. Change directory to project root:

   ```bash
   cd yii2-app-sudoku
   ```

5. Run command:

   ```bash
   vagrant up
   ```
6. Connect to vagrant by ssh and start sudoku websocket server:

   ```bash
   vagrant ssh
   cd /app
   php yii sudoku/server/listen
   ```
   
That's all. You just need to wait for completion! After that you can access project locally by URLs:
* frontend: http://sudoku.local/
* backend: http://admin.sudoku.local/
   
#### Manual for Windows users

1. Install [VirtualBox](https://www.virtualbox.org/wiki/Downloads)
2. Install [Vagrant](https://www.vagrantup.com/downloads.html)
3. Reboot
4. Create GitHub [personal API token](https://github.com/blog/1509-personal-api-tokens)
5. Prepare project:
   * download repo [yii2-app-sudoku](https://github.com/wmsamolet/yii2-app-sudoku/archive/master.zip)
   * unzip it
   * go into directory `yii2-app-sudoku-master/vagrant/config`
   * copy `vagrant-local.example.yml` to `vagrant-local.yml`

6. Place your GitHub personal API token to `vagrant-local.yml`

7. Open terminal (`cmd.exe`), **change directory to project root** and run command:

   ```bash
   vagrant up
   ```
   
   (You can read [here](http://www.wikihow.com/Change-Directories-in-Command-Prompt) how to change directories in command prompt) 

8. Connect to vagrant by ssh and start sudoku websocket server:

   ```bash
   vagrant ssh
   cd /app
   php yii sudoku/server/listen
   ```
   
That's all. You just need to wait for completion! After that you can access project locally by URLs:
* frontend: http://sudoku.local/
* backend: http://admin.sudoku.local/

DIRECTORY STRUCTURE
-------------------

```
common
    bootstrap/           contains bootstrap classes (DI container)
    config/              contains shared configurations
    mail/                contains view files for e-mails
    models/              contains model classes used in both backend and frontend
    tests/               contains tests for common classes    
console
    config/              contains console configurations
    controllers/         contains console controllers (commands)
    migrations/          contains database migrations
    models/              contains console-specific model classes
    runtime/             contains files generated during runtime
apps/backend
    assets/              contains application assets such as JavaScript and CSS
    config/              contains backend configurations
    controllers/         contains Web controller classes
    models/              contains backend-specific model classes
    runtime/             contains files generated during runtime
    tests/               contains tests for backend application    
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
apps/frontend
    assets/              contains application assets such as JavaScript and CSS (Sudoku frontend)
    config/              contains frontend configurations
    controllers/         contains Web controller classes
    models/              contains frontend-specific model classes
    runtime/             contains files generated during runtime
    tests/               contains tests for frontend application
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
    widgets/             contains frontend widgets
vendor/                  contains dependent 3rd-party packages
environments/            contains environment-based overrides
packages/                
    wmsamolet/           contains self-written packages (wmsamolet/*)
```
