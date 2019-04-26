# This creates a php6.5 image to run the bitbucket build pipeline
FROM centos:centos7
RUN yum update -y && yum install -y wget
RUN wget https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm && wget http://rpms.remirepo.net/enterprise/remi-release-7.rpm && rpm -Uvh remi-release-7.rpm epel-release-latest-7.noarch.rpm
RUN yum install yum-utils -y && yum-config-manager --enable remi-php72 && yum install -y git zip php72 php-zip php-xml php-mbstring php-bcmath && yum clean all && rm -rf /var/cache/yum
RUN ln /opt/remi/php72/root/usr/bin/php /usr/local/bin/php && \
    echo "extension=dom.so" >> /etc/opt/remi/php72/php.d/20-dom.ini && \
    echo "extension=mbstring.so" >> /etc/opt/remi/php72/php.d/20-mbstring.ini && \
    echo "extension=xml.so" >> /etc/opt/remi/php72/php.d/20-xml.ini && \
    echo "extension=xmlwriter.so" >> /etc/opt/remi/php72/php.d/20-xmlwriter.ini && \
    echo "extension=zip.so" >> /etc/opt/remi/php72/php.d/20-zip.ini && \
    echo "extension=bcmath.so" >> /etc/opt/remi/php72/php.d/20-bcmath.ini
RUN ln /usr/lib64/php/modules/dom.so /opt/remi/php72/root/usr/lib64/php/modules/dom.so && \
    ln /usr/lib64/php/modules/mbstring.so /opt/remi/php72/root/usr/lib64/php/modules/mbstring.so && \
    ln /usr/lib64/php/modules/xml.so /opt/remi/php72/root/usr/lib64/php/modules/xml.so && \
    ln /usr/lib64/php/modules/xmlwriter.so /opt/remi/php72/root/usr/lib64/php/modules/xmlwriter.so && \
    ln /usr/lib64/php/modules/zip.so /opt/remi/php72/root/usr/lib64/php/modules/zip.so && \
    ln /usr/lib64/php/modules/bcmath.so /opt/remi/php72/root/usr/lib64/php/modules/bcmath.so
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
#RUN git clone https://simon_massey@bitbucket.org/simon_massey/thinbus-php.git && cd thinbus-php/ && composer install
CMD ["php", "-a"]
