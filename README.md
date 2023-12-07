### News Aggregator
 **This is a _dockerized_ Laravel/React Full Stack application for a news aggregator that fetches news from 3 different sources.**

- News API
- Guardian API
- New York Times API

#### Features:
- Fetch News from 3 different sources and merge them in a unified structure
  - Show Thumbnail, Title, Excerpt of the content, date and author if applicable
  - Link to actual article on the corresponding website
- Filter by Sources
- Filter by Start Date and End Date
- Filter by Categories
- Filter by Search query string (keyword)
- Authentication (Login/Register)
- Save User Preference (if user logged in)
  - Improvement: Save preference in LocalStorage
- Paginates Fetched news
- Dockerized Laravel and React Application

#### Endpoints:
##### Available in postman collection present in the repository

#### How to get Started:
##### Prerequisites:
- Docker -> [Install Link](https://docs.docker.com/engine/install/ubuntu/)
- Docker Compose -> [Install Link](https://docs.docker.com/compose/install/)
- MySQL Version 8.0.35
- Git clone this repository
#### Installation Steps
- Cd into `news-aggregator` and check `.env` file variable MYSQL_DATA to match your system's (Note: using Mysql Version **8.0.35**)
  - You might need to create a database named `news-aggregator`
- directory and run Command: `docker-compose up -d`
- Run migration using command: `docker exec -it news_backend_container php artisan migrate`
- Access Frontend URL using `localhost:3000`
