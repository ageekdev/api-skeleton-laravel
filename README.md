<h1 align="center">:vendor_name - :project_title</h1>
<p align="center">
<a href="https://laravel.com/docs/10.x"><img src="https://img.shields.io/badge/Laravel-10.x-red?style=flat-square&logo=Laravel" alt="Laravel 10"></a>
<a href="https://www.php.net/releases/8.2/en.php"><img src="https://img.shields.io/badge/php-%5E8.2-blue?style=flat-square&logo=php" alt="PHP 8.2"></a>
</p>

<!--delete-->
---
This repo can be used to scaffold a Laravel package. Follow these steps to get started:

1. Press the "Use template" button at the top of this repo to create a new repo with the contents of this skeleton.
2. Run "php ./configure.php" to run a script that will replace all placeholders throughout all the files.
---
<!--/delete-->
This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Requirements

- [PHP 8.2](https://www.php.net/releases/8.2/en.php)
- [Composer](https://getcomposer.org)
- [PostgreSQL](https://www.postgresql.org/)

## Local Development

If you want to work on this project on your local machine, you may follow the instructions below. These instructions assume you are serving the site using Laravel Valet out of your `~/Sites` directory:

1. Open your terminal and `cd` to your `~/Sites` folder
2. Clone into the `~/Sites/:project_slug` folder:
    ```bash
    git clone https://github.com/:vendor_slug/:project_slug.git
    ```
3. CD into the new directory you just created:
    ```bash
    cd :project_slug
    ```
4. Run the `setup.sh` bin script, which will take all the steps necessary to prepare your local install:
    ```bash
    ./bin/setup.sh
    ```
