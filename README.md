# 勤怠管理アプリ

## アプリケーション概要

Laravelを用いて作成した勤怠管理アプリです。  
一般ユーザーと管理者の2権限構成を採用し、勤怠登録から修正申請承認までを一貫して行うことができます。

### 一般ユーザー機能

- 会員登録
- ログイン / ログアウト
- メール認証
- 出勤 / 退勤登録
- 休憩開始 / 終了
- 勤怠一覧表示
- 勤怠詳細確認
- 勤怠修正申請
- 修正申請一覧

### 管理者機能

- 管理者ログイン
- 日次勤怠一覧
- スタッフ一覧
- スタッフ別勤怠一覧
- 勤怠詳細確認 / 修正
- 修正申請一覧
- 修正申請承認

---

## 環境構築

-1.リポジトリをクローン

git clone https://github.com/rikutomashio/attendance-app.git

cd attendance-app

-2.Docker起動

docker-compose up -d --build


-3.PHPコンテナに入る

docker-compose exec php bash

-4.Laravel初期設定

composer install

cp .env.example .env

php artisan key:generate

-5.DB設定（.env）

DB_CONNECTION=mysql

DB_HOST=mysql

DB_PORT=3306

DB_DATABASE=laravel_db

DB_USERNAME=laravel_user

DB_PASSWORD=laravel_pass

※.envのDB設定を必ず上記の内容に変更してください

-6.メール認証設定（Mailtrap）

本アプリではメール認証機能にMailtrapを使用しています。

 Mailtrapにアクセスし、アカウントを作成してください  
   https://mailtrap.io/

 Inboxを作成し、「SMTP Settings」を開きます

 表示されているSMTP情報をコピーし、.envに以下を設定してください

MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=ご自身のユーザー名
MAIL_PASSWORD=ご自身のパスワード
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=任意のメールアドレス
MAIL_FROM_NAME="${APP_NAME}"

 設定後、以下コマンドを実行してください

php artisan config:clear

 認証メールはMailtrapのInbox上で確認できます

-7.マイグレーション（シーディングも含めて）

php artisan migrate:fresh --seed 

※既存データを削除して初期状態から構築されます

-8.ストレージリンク

php artisan storage:link

-9.動作確認

- アプリケーション
  - http://localhost

- phpMyAdmin
  - http://localhost:8080

---

## 初期データ

シーディングにより以下のテストユーザーが作成されます。

- 管理者
メールアドレス: admin@test.com  
パスワード: password

- 一般ユーザー
メールアドレス: user@test.com  
パスワード: password

---

# 使用技術


- PHP 8.1 
- Laravel 8.75 
- Laravel Fortify 
- MySQL 8.0.26 
- Mailtrap 
- Feature Test
- FormRequest

---

# 開発環境

- アプリケーション    [http://localhost](http://localhost)           
  
- phpMyAdmin  [http://localhost:8080](http://localhost:8080) 


---

# テスト

Featureテストを中心に実装しています。

## テスト実行

php artisan test

---

# 認証構成

一般ユーザーと管理者で認証を分離しています。

- users テーブル
- admins テーブル

管理者認証には admin guard を使用しています。

---

# ER図

※ ER図を追加予定

---

# URL一覧

## 一般ユーザー

| 機能     | URL                            |
| ------ | ------------------------------ |
| 会員登録   | /register                      |
| ログイン   | /login                         |
| 勤怠登録   | /attendance                    |
| 勤怠一覧   | /attendance/list               |
| 勤怠詳細   | /attendance/detail/{id}        |
| 申請一覧   | /stamp_correction_request/list |

## 管理者

| 機能        | URL                                                               |
| --------- | ----------------------------------------------------------------- |
| ログイン       | /admin/login                                                      |
| 勤怠一覧       | /admin/attendance/list                                            |
| スタッフ一覧    | /admin/staff/list                                                 |
| スタッフ別勤怠一覧 | /admin/attendance/staff/{id}                                      |
| 勤怠詳細       | /admin/attendance/{id}                                            |
| 申請一覧       | /stamp_correction_request/list                                    |
| 修正申請承認    | /stamp_correction_request/approve/{attendance_correct_request_id} |

※　申請一覧画面は一般ユーザーと管理者で同じパスを使用。認証ミドルウェアで区別

---

# 工夫した点

- 一般ユーザーと管理者で認証を完全分離
- admin guard を利用した権限制御
- users / admins テーブル分離構成
- Blade + CSS分離による画面ごとの責務整理
- Featureテストによる主要機能の品質担保
- Docker環境による開発環境統一
- Mailtrapを利用したメール認証確認
- 勤怠修正申請時に変更前(before_*)・変更後(requested_*) を保持し、
  差分履歴を確認できる設計を採用
- 修正申請承認者は admins テーブルへ外部キー制約を設定し、
  管理者のみ承認可能な構成を実装
- user_id と work_date にユニーク制約を設定し、
  1日1勤怠レコードとなるよう設計

---

## 作成者

名前 真尾陸人

