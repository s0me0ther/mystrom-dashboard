FROM rust:alpine AS collector

# add dependencies to build the mystrom-collector
RUN apk add --no-cache \
      git \
      build-base \
      openssl-dev \
      openssl-libs-static

# clone collector repository
RUN git clone https://github.com/s0me0ther/mystrom-collector.git

# build collector binary to /mystrom-collector/target/release/mystrom-collector
RUN cd mystrom-collector && cargo build --release
