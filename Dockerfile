FROM node:20-slim

# Install PHP with required extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
    ca-certificates php-cli php-curl php-json php-mbstring \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Copy PHP SDK and dependencies
COPY composer.json ./
COPY vendor/ ./vendor/
COPY src/ ./src/
COPY .env.ini ./

# Copy Next.js app
COPY web/ ./web/

# Install and build Next.js
WORKDIR /app/web
RUN npm ci && npm run build

EXPOSE 3000
CMD ["npm", "start"]
