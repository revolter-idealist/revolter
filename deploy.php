<?php

namespace Deployer;

require 'recipe/common.php';
require 'recipe/symfony3.php';

set('git_cache', false);

// Configuration

set('repository', 'https://github.com/revolter-idealist/revolter.git');
// -- Общие папки
set('shared_dirs', array_merge(['web/texts'], get('shared_dirs')));
//set('shared_files', []);
//set('writable_dirs', []);

// Servers

server('prod', 'domain.com')
    ->user('username')
    ->identityFile()
    ->set('deploy_path', '/var/www/revolter')
    ->stage('prod');

set('default_stage', 'dev');
set('keep_releases', 5);

if(file_exists('dep_conf.php')) {
	include('dep_conf.php');
}	

set('composer_command', '/bin/composer.phar');
set('copy_dirs', ['vendor']);
set('writable_use_sudo', true);
set('writable_mode', 'chown');

// Tasks

// Перезапустить PHP после успешного деплоя
task('reload:php7', function() {
    run('service php7.0-fpm restart');
});
// После деплоя перезапустим php
after('deploy:symlink', 'reload:php7');
after('rollback', 'reload:php7');
// После отката на прошлый релиз - тоже перезапустим его


after('deploy', 'success');


// Ссылки на тексты
task('text-links', function() {
    run("mkdir -p {{release_path}}/web/texts");
           cd("{{release_path}}/web/texts");
    run("ln -sf {{release_path}}/vendor/revolter-idealist/distributed-community");
    run("ln -sf {{release_path}}/vendor/revolter-idealist/method-of-paper-leaflets");
    run("ln -sf {{release_path}}/vendor/revolter-idealist/revolter-social-project");
    run("ln -sf {{release_path}}/vendor/revolter-idealist/stop-revolution");
});
after('deploy:vendors', 'text-links');
