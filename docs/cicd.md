# CI/CD Setup

This project includes a production GitHub Actions workflow in [`.github/workflows/production.yml`](../.github/workflows/production.yml).

## What The Workflow Does

1. Runs Laravel tests on every push to the `production` branch.
   The CI test job uses a GitHub Actions MySQL service so test execution is closer to production.
2. Builds frontend assets to catch Vite or Tailwind issues.
3. Builds and pushes a Docker image to Docker Hub.
4. Connects to the production server over SSH.
5. Pulls the new image and runs the updated container.

## Required GitHub Secrets

### Required

- `DOCKER_USERNAME`
- `DOCKER_PASS`
- `APP_NAME`
- `APP_SLUG`
- `HOST`
- `HOST_USER`
- `SSH_PASS`
- `APP_KEY`
- `APP_URL`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE_PRODUCTION`
- `DB_USERNAME_PRODUCTION`
- `DB_PASSWORD_PRODUCTION`

### Optional

- `APP_PORT`
- `MAIL_MAILER`
- `MAIL_SCHEME`
- `MAIL_HOST`
- `MAIL_PORT`
- `MAIL_USERNAME`
- `MAIL_PASSWORD`
- `MAIL_FROM_ADDRESS`
- `MAIL_FROM_NAME`
- `AWS_ACCESS_KEY_ID`
- `AWS_SECRET_ACCESS_KEY`
- `AWS_DEFAULT_REGION`
- `AWS_BUCKET`
- `AWS_USE_PATH_STYLE_ENDPOINT`

## Secret Notes

- `APP_SLUG` should be lowercase and safe for Docker image and container names.
  Example: `office-asset-request`
- `APP_NAME` is the display name Laravel uses in the application.
  Example: `Office Asset Request`
- `APP_PORT` defaults to `9001` if you do not set it.
- The workflow assumes Docker is already installed on the production server.

## Docker Image Tags

Each production deployment pushes two tags:

- `${GITHUB_SHA}`
- `production-latest`

## Deployment Behavior

- the old production container is removed before the new one starts
- storage is mounted from `/home/actions/apps/<app-slug>/storage`
- Laravel migrations run automatically through the container entrypoint
- the workflow waits for the container healthcheck before marking deployment successful
