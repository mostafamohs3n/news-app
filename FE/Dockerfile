FROM node:21.3-alpine

WORKDIR /app

# Copy important files
COPY package.json .
COPY ./docker ./docker

# Expose port 3000
EXPOSE 3000

# Entrypoint that does npm install and start
RUN chmod +x "docker/entrypoint.sh"
ENTRYPOINT ["sh", "docker/entrypoint.sh"]