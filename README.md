# DEMO

#### ふうがくんBOT
優秀な愛犬である、ふうがくんが、毎日の餌代稼ぐために
天気の予測、わからないことを教えてくれます。

# functions
・天気検索機能
(雨が降る場合、傘が必要か聞いてくれます)
・qiitaの記事検索
・qiitaのトレンド検索
・wikipediaの記事検索
・チャット機能

# Requirement
・CentOS 7.9
・Apache2.4
・PHP7.3
・Mysql5.7

# APIs
・Line
・OpenWeatherMap
・Chatplus

# Installation
**Apache2.4**
```
//インストールされている古いhttpdの削除
systemctl stop httpd
systemctl disable httpd.service
yum remove httpd
yum remove httpd-tools

//yumの追加
yum -y install epel-release
vi /etc/yum.repos.d/epel.repo
  （[epel]のenabledを0に変更する）
yum -y install https://repo.ius.io/ius-release-el7.rpm
vi /etc/yum.repos.d/ius.repo
  （[ius]のenabledを0に変更する）

//サービス登録と動作確認
systemctl enable httpd.service
systemctl start httpd
```

**Php 7.3**
```
//epelのインストール
 yum install epel-release
rpm -Uvh http://rpms.famillecollet.com/enterprise/remi-release-7.rpm
//現在のPHPを削除
cp /etc/php.ini /etc/php-old.ini
yum remove php-*
//インストール
yum install —disablerepo=* —enablerepo=epel,remi,remi-safe,remi-php73 /
//設定の復元
vi /etc/php.ini
```

**Mysql 5.7**
```
mariaDBがインストールされているか？
rpm -qa | grep maria
//mariaDBの削除
sudo yum remove mariadb-libs
sudo rm -rf /var/lib/mysql
//公式リポジトリの追加
sudo rpm -Uvh http://dev.mysql.com/get/mysql57-community-release-el7-11.noarch.rpm
//mysqlをインストール
sudo yum install —enablerepo=mysql57-community mysql-community-server
//バージョン確認
mysqld —version
//自動起動
systemctl enable mysqld.service
//起動
systemctl start mysqld.service
```

# Database
```
CREATE TABLE `users`(
    `id` int(11) AUTO_INCREMENT, PRIMARY KEY(id)
    ,`line_accesstoken` varchar(255) NOT NULL
    ,`latitude` decimal(8,6) DEFAULT NULL
    ,`longitude` decimal(9,6) DEFAULT NULL
    ,`departure_time` datetime DEFAULT NULL
);
```
# Author
作成者：村田貴司
 E-mail：fuuga.090690906@gmail.com


