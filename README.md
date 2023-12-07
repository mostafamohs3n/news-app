# News App

### Introduction
**News** is a web application that features articles from different sources across the web, it uses third party news outlets, providing a real-time update for what is happening in the world, you are able to filter the news by different factors such as (author, date, news source, category).
In **News App** you are able to save your preferences, so that when you are logged in the next time, you catch up to where you left off!


##### Supported News Sources
- News API https://newsapi.org/register
- The Guardian https://bonobo.capi.gutools.co.uk/register/developer
- New York Times https://developer.nytimes.com/accounts/create

### Technical Stack
- PHP (Laravel 10.0)
- JavaScript (React 18.2)
- MySQL 8.0.35
- Docker
- Docker Compose

### General Technical Notes

#### Database
The database is built in a way so that it will be easy to add new article sources, new categories, new external sources, and support querying large datasets through the usage of foreign keys and indices among others.
List of main database tables:
- `articles`
- `article_categories`
- `article_sources`
- `article_external_sources`
- `users`
- `user_preferences`


#### Backend

- Throughout, we make sure the **code is clean** and adheres to best practices and SOLID principles are incorporated when applicable.
- In all the articles controllers, we use a **Service Class** to communicate with the database layer or the any third party API.
- When fetching articles, and the database has **no records** matching the user search, we will **fetch those articles from the third parties itself directly**, this is to ensure a good user experience. (We also *cache* those results in memory to avoid hitting the external API unnecessarily)
- We make use of the **seeding**, to add the necessary data in the database seamlessly, the seeders are:
  - Article Category
  - Article Source
  - Article External Source
- All the tables, column changes in the database are all part of **migration classes** to ensure consistency across all developers of the app.
- We scrap all the articles from third party news outlets and run a **scheduled command** `app:scrap-articles`
  - The command runs every 12 hours through the **Laravel Scheduler**
  - During this command, we run it for each article source and looped for 10 pages of the news, with max results of 100.
  - We run the command for the articles that were published in the last 30 days only to ensure relevance.
  - For News API, we fetch the articles for each category that is supported, to ensure wealth of data for all categories.
- For Article Items, since they are fetched from different sources, an **Adapter class** is implemented to be used to unify the data structure for each article item entry
  Also there is a **DTO for the Article Item** to ensure unified interface to access the attributes of each entry.
- Every class has an intended layer to use it to ensure encapsulation, example:
  - The controller should not be able to use the Eloquent model directly, rather the controller should call the service and the service is responsible for doing that.
- There are **enums** to avoid static values.
- **Helpers and utilities** are in a separate class and namespace and used across the app to avoid repeating code.

#### Frontend

The frontend layer is completely separated in a separate project, divided into reusable components used across the page, and makes use of the following main features and packages:
- `react, bootstrap, react-bootstrap, react-hook-forms, react-hot-toast, bootswatch` among others

#### Docker

The docker setup consists of 3 main services, with a Makefile for ease of use.
- Laravel Backend (apache2-foreground and laravel scheduler through supervisor)
- React Frontend
- Mysql Database

### Installation

#### Prerequisites

- Docker -> [Install Link](https://docs.docker.com/engine/install/ubuntu/)
- Docker Compose -> [Install Link](https://docs.docker.com/compose/install/)
- MySQL Version 8.0.35
- Git clone/download this repository

#### Installation Steps

- Go to the directory where you cloned/downloaded this repository.
- Run the following command: `cp ./BE/.env.example ./BE/.env`
- Run: `make up` to build all the services and up the containers
- Run: `make backend-setup` to generate laravel application key, migrate and seed database.
- (Optional) run `docker exec -it news_backend_container bash -c "php artisan app:scrap-articles"` to scrap articles from the news sources.
- Make sure that all services are up by running command: `make ps`
- Access the Frontend URL: http://localhost:3000