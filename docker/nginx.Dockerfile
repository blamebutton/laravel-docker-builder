# Vite.js build
FROM node:lts-alpine AS node

WORKDIR /app

# Cache layer for "npm ci"
COPY /package.json /package-lock.json /app/
RUN npm ci
# Copy JavaScript
COPY /vite.config.js /app/
COPY /resources/ /app/resources/
# Build using Vite.js
RUN npm run build

FROM nginx:1-alpine

WORKDIR /app/public

COPY --from=node /app/public/build/ /app/public/build/

COPY /public/ /app/public/
