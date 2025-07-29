# 勤怠管理システム

## 環境構築

### Docker ビルド

1. 勤怠管理システムプロジェクトをクローン
```bash
git clone git@github.com:yoshihub/kintai.git
```
2. 勤怠管理システムプロジェクトのディレクトリに移動
```bash
cd kintai
```

3. DockerDesktop アプリを立ち上げる
4. 以下のコマンドを実行して Docker をビルド
    ```bash
    docker-compose up -d --build
    ```

### Laravel 環境構築

1. コンテナに入る

    ```bash
    docker-compose exec php bash
    ```

2. 依存パッケージのインストール

    ```bash
    composer install
    ```
3. Fortify のインストール

   ```bash
   composer require laravel/fortify
   ``` 

4. 環境変数の設定

    - 「.env.example」ファイルを 「.env」ファイルに命名を変更。または、新しく.env ファイルを作成
    - .env に以下の環境変数を追加
    - メール認証は mailtrap を使用
        ```
        DB_CONNECTION=mysql
        DB_HOST=mysql
        DB_PORT=3306
        DB_DATABASE=laravel_db
        DB_USERNAME=laravel_user
        DB_PASSWORD=laravel_pass

        MAIL_MAILER=smtp
        MAIL_HOST=sandbox.smtp.mailtrap.io
        MAIL_PORT=2525
        MAIL_USERNAME=mailtrapのユーザー名
        MAIL_PASSWORD=mailtrapのパスワード
        MAIL_ENCRYPTION=tls
        MAIL_FROM_ADDRESS=flema@example.com
        MAIL_FROM_NAME="勤怠管理システム"
        ```

5. アプリケーションキーの作成

    ```bash
    php artisan key:generate
    ```

6. マイグレーションの実行

    ```bash
    php artisan migrate
    ```

7. シーディングの実行

    ```bash
    php artisan db:seed
    ```

## 使用技術(実行環境)

-   PHP 7.4.9
-   Laravel 8.83.29
-   MySQL 8.0.26

## ER 図
![ER図](https://github.com/user-attachments/assets/bbd15235-6849-46fc-8f15-56ac92092f3c)

## テーブル仕様
## usersテーブル

| カラム名                     | 型              | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY |
|------------------------------|-----------------|:-----------:|:----------:|:--------:|:-----------:|
| id                           | bigint unsigned |     ◯       |            |    ◯     |             |
| name                         | varchar(255)    |             |            |    ◯     |             |
| email                        | varchar(255)    |             |     ◯      |    ◯     |             |
| email_verified_at            | timestamp       |             |            |          |             |
| password                     | varchar(255)    |             |            |    ◯     |             |
| remember_token               | varchar(100)    |             |            |          |             |
| two_factor_secret            | text            |             |            |          |             |
| two_factor_recovery_codes    | text            |             |            |          |             |
| created_at                   | timestamp       |             |            |          |             |
| updated_at                   | timestamp       |             |            |          |             |

## attendancesテーブル

| カラム名     | 型               | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY  |
|--------------|------------------|:-----------:|:----------:|:--------:|:------------:|
| id           | bigint unsigned  |     ◯       |            |    ◯     |              |
| user_id      | bigint unsigned  |             |            |    ◯     | users(id)    |
| date         | date             |             |            |    ◯     |              |
| clock_in     | time             |             |            |          |              |
| clock_out    | time             |             |            |          |              |
| status       | tinyint(1)       |             |            |    ◯     |              |
| note         | text             |             |            |          |              |
| created_at   | timestamp        |             |            |          |              |
| updated_at   | timestamp        |             |            |          |              |

## breaksテーブル

| カラム名        | 型               | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY       |
|-----------------|------------------|:-----------:|:----------:|:--------:|:-----------------:|
| id              | bigint unsigned  |     ◯       |            |    ◯     |                   |
| attendance_id   | bigint unsigned  |             |            |    ◯     | attendances(id)   |
| break_start     | time             |             |            |    ◯     |                   |
| break_end       | time             |             |            |          |                   |
| created_at      | timestamp        |             |            |          |                   |
| updated_at      | timestamp        |             |            |          |                   |

## adminsテーブル

| カラム名           | 型              | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY |
|--------------------|-----------------|:-----------:|:----------:|:--------:|:-----------:|
| id                 | bigint unsigned |     ◯       |            |    ◯     |             |
| name               | varchar(255)    |             |            |    ◯     |             |
| email              | varchar(255)    |             |     ◯      |    ◯     |             |
| email_verified_at  | timestamp       |             |            |          |             |
| password           | varchar(255)    |             |            |    ◯     |             |
| created_at         | timestamp       |             |            |          |             |
| updated_at         | timestamp       |             |            |          |             |

## stamp_correction_requestsテーブル

| カラム名           | 型                          | PRIMARY KEY | UNIQUE KEY | NOT NULL |   FOREIGN KEY   |
| -------------- | -------------------------- | :---------: | :--------: | :------: | :-------------: |
| id             | bigint unsigned            |      ○      |            |     ○    |                 |
| user\_id       | bigint unsigned            |             |            |     ○    |    users(id)    |
| attendance\_id | bigint unsigned            |             |            |     ○    | attendances(id) |
| start\_time    | time                       |             |            |     ○    |                 |
| end\_time      | time                       |             |            |     ○    |                 |
| breaks         | json                       |             |            |          |                 |
| note           | text                       |             |            |     ○    |                 |
| status         | enum('pending','approved') |             |            |     ○    |                 |
| created\_at    | timestamp                  |             |            |          |                 |
| updated\_at    | timestamp                  |             |            |          |                 |





## アクセス URL

-   開発環境：http://localhost/login
-   phpMyAdmin：http://localhost:8080/

## 認証済みユーザーアカウント
管理者画面アクセス用アカウント
- メールアドレス admin@example.com
- パスワード    hogehoge1

一般ユーザーアクセスアカウント
- メールアドレス tanaka@example.com
- パスワード    password123
