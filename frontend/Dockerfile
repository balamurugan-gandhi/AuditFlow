FROM node:20-alpine

# Set working directory
WORKDIR /app

# Copy ONLY package files first (for cache efficiency)
COPY package.json package-lock.json* ./

# Install dependencies INSIDE the container (Linux binaries)
RUN npm install

# Copy the rest of the frontend source code
#COPY . .

# Expose Vite dev port
EXPOSE 5173

# Start Vite and listen on all interfaces
CMD ["npm", "run", "dev", "--", "--host"]