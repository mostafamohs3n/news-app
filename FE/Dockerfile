FROM node:16.19.1-alpine

WORKDIR /app/frontend
COPY ./ /app/frontend


RUN apk update && apk add bash
RUN npm install


EXPOSE 3000
CMD ["npm", "start"]