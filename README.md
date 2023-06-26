# bilemo

<a href="https://codeclimate.com/github/m-perraud/bilemo-new/maintainability"><img src="https://api.codeclimate.com/v1/badges/f1485ae72124dc2d6446/maintainability" /></a> [![Codacy Badge](https://app.codacy.com/project/badge/Grade/afa79e5965254d1e839932102ecd00af)](https://app.codacy.com/gh/m-perraud/bilemo-new/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)

### Install
In order to get started you first need to clone the project:
- `git clone https://github.com/m-perraud/bilemo-new.git`

We will need composer for the project. 
- `at the root, run composer install`


### Prerequisites

-   Composer **version 2** and superior
-   PHP 7.2 and superior
-   A MySQL Server up and running
-   Symfony 5.4 and superior


### Database

The database informations are stored in the .env file :

- `DATABASE_URL="mysql://root:@127.0.0.1:3306/bilemo"`

You have to modify those informations if you won't use the same. 
To set up the database, you will need to follow these steps : 

• Create the database if not already done : 
- `php bin/console doctrine:database:create`

• Make the migration : 
- `php bin/console doctrine:migrations:migrate`

• Get the data from the fixtures : 
- `php bin/console doctrine:fixtures:load`

• Don't forget to generate the SSH keys and add a passphrase key : 
- `JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem`
- `JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem`
- `JWT_PASSPHRASE=bilemo`

 
 ### Documentation
  
 Once the project installed, you'll be able to access the doc and try the API following that link : 
  - `https://127.0.0.1:8000/api/doc/`
 
  
 


 
