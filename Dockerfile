FROM php:7.2-cli

LABEL version="0.0.2"

RUN apt-get update && \
  apt-get install -y libgconf-2-4 curl libreadline-dev && \
  apt-get install -y git

# Cleanup leftovers
RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

WORKDIR /app-tools

COPY . /app-tools