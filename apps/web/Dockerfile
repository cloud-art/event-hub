FROM node:24-alpine
ENV NEXT_TELEMETRY_DISABLED=1
WORKDIR /app
COPY package*.json ./
RUN npm ci --no-audit --no-fund
COPY app app
COPY postcss.config.mjs tsconfig.json ./
CMD ["npm", "run", "dev", "--", "--hostname", "0.0.0.0"]
