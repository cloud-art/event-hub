FROM alpine:3.22 AS cms
RUN apk add --no-cache php83 php83-session php83-dom php83-xml php83-xmlreader php83-xmlwriter php83-mbstring php83-iconv php83-mysqli php83-gd php83-curl php83-openssl php83-zip php83-phar php83-tokenizer php83-simplexml php83-ctype php83-fileinfo php83-opcache php83-sockets php83-zlib php83-bcmath php83-intl
WORKDIR /www
COPY serve.sh /opt/serve.sh
CMD ["sh", "/opt/serve.sh"]

FROM alpine:3.22 AS wizard
RUN apk add --no-cache chromium nodejs npm && npm install --prefix /opt --no-audit --no-fund playwright-core@1.55.0
COPY wizard.cjs /opt/wizard.cjs
ENTRYPOINT ["node", "/opt/wizard.cjs"]
