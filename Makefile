up:
	docker-compose up -d
build:
	docker-compose build --no-cache --force-rm
stop:
	docker-compose stop
ps:
	docker-compose ps
backend-setup:
	docker exec -it news_backend_container bash -c "php artisan key:generate"
	docker exec -it news_backend_container bash -c "php artisan migrate"
	docker exec -it news_backend_container bash -c "php artisan db:seed"
backend-migrate:
	docker exec -it news_backend_container bash -c "php artisan migrate"
backend-seed:
	docker exec -it news_backend_container bash -c "php artisan migrate"
	docker exec -it news_backend_container bash -c "php artisan db:seed"
exec:
	docker exec -it news_$(SERVICE)_container bash
logs:
	docker-compose logs -f $(SERVICE)