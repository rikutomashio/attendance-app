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

# 環境構築

## Dockerビルド

git clone git@github.com:******/******.git
cd attendance-app
docker-compose up -d --build

## Laravel環境構築

docker-compose exec php bash

composer install
cp .env.example .env
php artisan key:generate

## データベース設定

php artisan migrate --seed

## シンボリックリンク作成

php artisan storage:link

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

# メール認証

Mailtrapを利用してメール認証機能を実装しています。

会員登録後、Mailtrap上で認証メールを確認できます。

---

# 管理者ログイン情報

email: admin@example.com
password: password

---

# 工夫した点

- 一般ユーザーと管理者で認証を完全分離
- admin guard を利用した権限制御
- users / admins テーブル分離構成
- Blade + CSS分離による画面ごとの責務整理
- Featureテストによる主要機能の品質担保
- Docker環境による開発環境統一
- Mailtrapを利用したメール認証確認

---
