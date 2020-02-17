FROM lsiobase/nginx:3.10

# set version label
ARG BUILD_DATE
ARG VERSION
ARG GROCY_RELEASE
LABEL build_version="Linuxserver.io version:- ${VERSION} Build-date:- ${BUILD_DATE}"
LABEL maintainer="alex-phillips, homerr"

RUN \
 echo "**** install build packages ****" && \
 apk add --no-cache --virtual=build-dependencies \
	git \
	composer \
	yarn && \
 echo "**** install runtime packages ****" && \
 apk add --no-cache \
	curl \
	php7 \
	php7-gd \
	php7-pdo \
	php7-pdo_sqlite \
	php7-opcache \
	php7-apcu \
	php7-tokenizer && \
 echo "**** install grocy ****" && \
 mkdir -p /app/grocy && \
# if [ -z ${GROCY_RELEASE+x} ]; then \
#	GROCY_RELEASE=$(curl -sX GET "https://api.github.com/repos/grocy/grocy/releases/latest" \
#	| awk '/tag_name/{print $4;exit}' FS='[""]'); \
# fi && \
 echo "**** install grocy from fork master branch!! ****" && \
 curl -o \
	/tmp/grocy.tar.gz -L \
	#"https://github.com/zebardy/grocy/archive/${GROCY_RELEASE}.tar.gz" && \
	"https://github.com/zebardy/grocy/archive/master.tar.gz" && \
 tar xf \
	/tmp/grocy.tar.gz -C \
	/app/grocy/ --strip-components=1 && \
 cp -R /app/grocy/data/plugins \
	/defaults/plugins && \
 echo "**** install composer packages ****" && \
 composer install -d /app/grocy --no-dev && \
 echo "**** install yarn packages ****" && \
 cd /app/grocy && \
 yarn && \
 echo "**** cleanup ****" && \
 apk del --purge \
	build-dependencies && \
 rm -rf \
	/root/.cache \
	/tmp/*

# copy local files
COPY root/ /
#COPY ./DatabaseService.php /app/grocy/services/
#COPY ./StockController.php /app/grocy/controllers/

# ports and volumes
EXPOSE 6781
VOLUME /config
