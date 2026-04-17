# Office Asset Request

Office Asset Request is a Laravel 13 application for managing internal asset requests, approvals, issuance, returns, stock adjustments, notifications, and reporting.

## Project Purpose

This project was built mainly as a portfolio project to showcase my understanding of:

- roles
- permissions
- policies
- workflow-based authorization
- thin controllers with business logic moved into services

The core learning focus was building a role-aware Laravel application without relying on a starter kit, and making authorization a visible part of the product instead of hidden setup.

## What The Project Does

The system supports a full internal office asset workflow:

- staff submit asset requests
- department managers review and approve or reject requests from their department
- admins perform final approval, issue assets, record returns, and manage stock adjustments
- users receive in-app notifications for important workflow changes
- admins and managers can access reporting screens for stock, requests, issues, and low-stock monitoring
- admins can manage users, roles, and permissions from inside the application

## Main Roles

- `Super Admin`
- `Department Manager`
- `Staff`

These roles are used together with Spatie permissions and Laravel policies to control:

- route access
- record-level authorization
- dashboard access
- department-scoped workflow actions

## Key Features

- manual authentication flow
- role-based dashboard redirects
- Spatie roles and permissions
- Laravel policies for record ownership and department scoping
- staff request workflow
- manager review workflow
- admin approval, issuing, returns, and stock adjustments
- notification center with deep links
- reports and dashboard metrics
- Docker support
- CI/CD pipeline for production deployment

## Stack

- PHP 8.4
- Laravel 13
- MySQL
- Tailwind CSS + Vite
- Pest
- Spatie Laravel Permission

## Project Structure Notes

The project follows a separation style where:

- validation lives in Form Requests
- business logic lives in services
- controllers stay thin

That structure was intentional because part of the goal was to show clean Laravel application design alongside authorization work.

## Setup

See [setup.md](setup.md) for:

- local setup
- Docker setup
- MySQL notes
- demo account details
- useful project commands

## CI/CD

A production deployment workflow is included in [`.github/workflows/production.yml`](.github/workflows/production.yml).

More details are in [docs/cicd.md](docs/cicd.md).
