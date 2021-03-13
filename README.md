# DEMO

### ふうがくんBOT
優秀な愛犬である、ふうがくんが、毎日の餌代稼ぐために
天気の予測、わからないことを教えてくれます。

# functions
### ・天気検索機能
<img width="685" alt="スクリーンショット 2021-03-01 23 09 45" src="https://user-images.githubusercontent.com/69385017/109510276-2910d300-7ae5-11eb-8e3e-1ed7842cfb8e.png">


#### 雨が降らない場合・・・

<img width="690" alt="スクリーンショット 2021-03-01 23 11 43" src="https://user-images.githubusercontent.com/69385017/109513029-0a600b80-7ae8-11eb-8e1e-0425f33d4894.png">


#### 雨が降る場合・・・
(こちらのみ携帯のみの機能で,傘が必要か聞いてくれます。)

<img width="432" alt="スクリーンショット 2021-03-01 23 45 35" src="https://user-images.githubusercontent.com/69385017/109513254-42674e80-7ae8-11eb-8da2-4ef9cb5845b5.png">


### ・qiitaの記事検索

<img width="685" alt="スクリーンショット 2021-03-01 23 12 50" src="https://user-images.githubusercontent.com/69385017/109512760-c66d0680-7ae7-11eb-8b57-f925994170db.png">


### ・qiitaのトレンド検索

<img width="680" alt="スクリーンショット 2021-03-01 23 13 36" src="https://user-images.githubusercontent.com/69385017/109512684-b2c1a000-7ae7-11eb-86a6-e68dd34b9e4c.png">


### ・wikipediaの記事検索

<img width="686" alt="スクリーンショット 2021-03-01 23 12 12" src="https://user-images.githubusercontent.com/69385017/109512594-97ef2b80-7ae7-11eb-985c-4fcc34aa7fbe.png">


### ・チャット機能

<img width="693" alt="スクリーンショット 2021-03-01 23 15 53" src="https://user-images.githubusercontent.com/69385017/109512836-d7b61300-7ae7-11eb-97bb-0ec9147fb2a9.png">


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
//設定の設定
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



## Thank you for watching!!
