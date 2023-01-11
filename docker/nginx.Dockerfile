FROM nginx:1-alpine

WORKDIR /app/public

COPY public/ /app/public/