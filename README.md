# Laravel Project

Welcome to the Laravel project! This project is built using the Laravel framework, a powerful PHP web application framework. Below, you'll find instructions on how to set up and start working on the project.

## Getting Started

Follow these steps to get the Laravel project up and running on your local machine.

### Prerequisites

- "php": "^8.1"
- Composer (https://getcomposer.org/)
- sqlite

### Installation

1. Clone the repository:

   ```bash
   git clone https://github.com/ugurcanclkl/todoplanner.git

   cd todoplanner

   composer install

   cp .env.example .env

    DB_CONNECTION=sqlite in your .env

    php artisan migrate:fresh --seed

   touch database/database.sqlite

    php artisan serve

    php artisan app:fetch-tasks-command

    go to http://localhost:8000/tasks

    https://ibb.co/LNZgBxv

    this link is the tasks image incase you dont want to go to localhost.


