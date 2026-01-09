# Railway デプロイガイド

このガイドでは、LaravelアプリケーションをRailwayにデプロイする手順を説明します。

## 前提条件

- Railwayアカウント（[railway.app](https://railway.app)で作成）
- GitHubアカウント（コードをプッシュするため）

## デプロイ手順

### 1. Railwayプロジェクトの作成

1. [Railway](https://railway.app)にログイン
2. 「New Project」をクリック
3. 「Deploy from GitHub repo」を選択
4. リポジトリを選択して接続

### 2. データベースの設定

1. Railwayダッシュボードで「+ New」をクリック
2. 「Database」→「Add PostgreSQL」を選択
3. データベースが作成されたら、「Variables」タブで以下の環境変数を確認：
   - `PGHOST`
   - `PGPORT`
   - `PGUSER`
   - `PGPASSWORD`
   - `PGDATABASE`

### 3. 環境変数の設定

プロジェクトの「Variables」タブで以下の環境変数を設定：

#### 必須環境変数

```env
APP_NAME="Message App"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://your-app-name.up.railway.app

# データベース設定（PostgreSQL）
# RailwayのPostgreSQLサービス名に合わせて変更してください
DB_CONNECTION=pgsql
DB_HOST=${{Postgres.PGHOST}}
DB_PORT=${{Postgres.PGPORT}}
DB_DATABASE=${{Postgres.PGDATABASE}}
DB_USERNAME=${{Postgres.PGUSER}}
DB_PASSWORD=${{Postgres.PGPASSWORD}}

# セッション設定
SESSION_DRIVER=database
SESSION_LIFETIME=120

# キャッシュ設定
CACHE_DRIVER=database
QUEUE_CONNECTION=database

# ログ設定
LOG_CHANNEL=stack
LOG_LEVEL=error

# Google OAuth設定（Googleアカウントでログインする場合）
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=https://your-app-name.up.railway.app/auth/google/callback
```

**重要**: 
- `APP_KEY`は以下のコマンドで生成：
  ```bash
  php artisan key:generate --show
  ```
- `APP_URL`はデプロイ後にRailwayが提供するURLに変更してください
- PostgreSQLサービス名が「Postgres」でない場合は、`${{Postgres.PGHOST}}`の部分を実際のサービス名に変更してください（例：`${{MyDatabase.PGHOST}}`）

**データベース接続の確認**:
- RailwayダッシュボードでPostgreSQLサービスの「Variables」タブを確認
- すべての`PG*`変数が正しく設定されているか確認
- プロジェクトの「Variables」タブで`DB_*`変数が正しく設定されているか確認

### 4. ストレージの設定（Cloudinary推奨）

**重要**: Railwayのサーバーは再デプロイのたびにクリーンな状態になるため、ローカルストレージ（`storage/app/public`）に保存された画像ファイルは消えてしまいます。画像を永続化するには、外部ストレージサービスを使用する必要があります。

#### Cloudinaryを使用する（推奨）

1. **Cloudinaryアカウントを作成**
   - [Cloudinary](https://cloudinary.com/)にアクセスして無料アカウントを作成

2. **認証情報を取得**
   - Cloudinaryダッシュボードから以下を取得：
     - `Cloud name`
     - `API Key`
     - `API Secret`

3. **Railwayの環境変数に追加**
   ```env
   CLOUDINARY_CLOUD_NAME=your-cloud-name
   CLOUDINARY_API_KEY=your-api-key
   CLOUDINARY_API_SECRET=your-api-secret
   ```

4. **パッケージのインストール**
   - 既に`composer.json`に追加済みです
   - Railwayにデプロイすると自動的にインストールされます

**注意**: `CLOUDINARY_CLOUD_NAME`が設定されていない場合、ローカルストレージ（`public`ディスク）が使用されます。開発環境では環境変数を設定しなくても動作します。

#### その他のオプション

- **Railway Volume**: 一時的な解決策ですが、再デプロイ時に削除される可能性があります
- **S3互換ストレージ**: AWS S3、DigitalOcean Spaces、Cloudflare R2などを使用

### 5. ビルドとデプロイ

Railwayは自動的に以下を実行します：

1. **ビルドフェーズ** (`nixpacks.toml`に基づく):
   - Composer依存関係のインストール（`--no-dev --optimize-autoloader --no-interaction`）
   - npm依存関係のインストール（`npm ci --prefer-offline --no-audit`）
   - アセットのビルド（`npm run build`）
   - 設定のキャッシュ（`config:cache`, `route:cache`, `view:cache`）- エラー時はスキップ

2. **デプロイフェーズ** (`Procfile`に基づく):
   - データベースマイグレーション実行（`php artisan migrate --force`）
   - ストレージリンク作成（`php artisan storage:link`）
   - アプリケーション起動（PHP組み込みサーバー + ルータースクリプト）

**注意**: `public/router.php`が静的ファイルを正しく配信するためのルータースクリプトです。

**注意**: 初回デプロイ時は、データベースマイグレーションが自動実行されます。

### 6. カスタムドメインの設定（オプション）

1. プロジェクトの「Settings」→「Domains」
2. 「Custom Domain」を追加
3. DNS設定をRailwayの指示に従って設定

## トラブルシューティング

### データベース接続エラー / テーブルがないエラー

- 環境変数が正しく設定されているか確認
- PostgreSQLサービスが起動しているか確認
- `DB_CONNECTION=pgsql`が設定されているか確認
- マイグレーションが実行されているか確認

**解決方法**:
1. Railwayダッシュボードで「Deployments」タブを開く
2. 最新のデプロイログを確認し、`php artisan migrate`が実行されているか確認
3. マイグレーションが実行されていない場合、Railwayのコンソールから手動で実行：
   - プロジェクトの「Settings」→「Service」→「Connect」をクリック
   - ターミナルで以下を実行：
     ```bash
     php artisan migrate --force
     ```
4. 環境変数`DB_*`が正しく設定されているか再確認
5. PostgreSQLサービスが正しく接続されているか確認

### ストレージエラー

- `php artisan storage:link`が実行されているか確認
- Volumeが正しくマウントされているか確認

### アセットが表示されない（404エラー）

- `npm run build`が実行されているか確認（ビルドログを確認）
- `public/build`ディレクトリが存在するか確認
- `APP_URL`が正しく設定されているか確認
- `public/router.php`が正しく機能しているか確認
- Viteのマニフェストファイル（`public/build/.vite/manifest.json`）が存在するか確認

**解決方法**:
1. Railwayのビルドログで`npm run build`が成功しているか確認
2. 環境変数`APP_URL`が正しいURLに設定されているか確認
3. デプロイを再実行してみる

### ログの確認

Railwayダッシュボードの「Deployments」タブでログを確認できます。

## 本番環境での推奨設定

1. **セキュリティ**:
   - `APP_DEBUG=false`
   - `APP_ENV=production`
   - HTTPSの使用（Railwayは自動で提供）

2. **パフォーマンス**:
   - 設定のキャッシュ（自動実行）
   - ルートのキャッシュ（自動実行）
   - ビューのキャッシュ（自動実行）

3. **監視**:
   - Railwayのメトリクスを確認
   - エラーログを定期的に確認

## 注意事項

- **SQLiteからPostgreSQLへの移行**: 現在のアプリはSQLiteを使用していますが、RailwayではPostgreSQLを使用します。環境変数で`DB_CONNECTION=pgsql`を設定してください。
- **既存データの移行**: 既存のSQLiteデータがある場合は、手動でPostgreSQLに移行する必要があります。
- **ファイルアップロード**: アバターやアイコン画像は、Railway Volumeまたは外部ストレージ（S3など）を使用してください。デフォルトでは`storage/app/public`に保存されますが、Volumeをマウントすることを推奨します。
- **Service Worker**: `public/sw.js`は自動的にデプロイされますが、HTTPS環境でのみ動作します。

## クイックスタート（簡易版）

1. Railwayでプロジェクトを作成
2. GitHubリポジトリを接続
3. PostgreSQLデータベースを追加
4. 環境変数を設定（上記参照）
5. デプロイを待つ

デプロイが完了すると、Railwayが提供するURLでアプリにアクセスできます。

