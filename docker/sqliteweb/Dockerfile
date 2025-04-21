FROM python:alpine

RUN pip install sqlite-web

VOLUME /data/app
WORKDIR /data/app

EXPOSE 8080

ENTRYPOINT ["sqlite_web", "--host=0.0.0.0", "--port=8080", "--no-browser"]
