
# Backend Sistem Manajemen Work Order

It's only for RestFull API's for Sistem Manajemen Work Order.




## Run Locally

Clone the project

```bash
  git clone https://github.com/vinss-droid/be-smwom.git
```

Go to the project directory

```bash
  cd be-smwom
```

Install dependencies

```bash
  composer install
```

Copy .env.example to .env

```bash
  cp .env.example .env
```

Generate app key

```bash
  php artisan key:generate
```

Set database name and configuration in .env file (make sure you have the empy database first)

`DB_HOST=`
`DB_PORT=`
`DB_DATABASE=`
`DB_USERNAME=`
`DB_PASSWORD=`

Migrate the database & seeder

```bash
  php artisan migrate:fresh --seed
```

Start the server

```bash
  php artisan serve
```


## Default Account

| Email | Password     | Role                |
| :-------- | :------- | :------------------------- |
| `pm@smwom.com` | `pm@smwom.com` | Production Manager |
| `op1@smwom.com` | `op1@smwom.com` | Operator |
| `op2@smwom.com` | `op2@smwom.com` | Operator |



## API Reference

#### Login

```http
  POST /auth/login
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `email` | `string` | **Required**. |
| `password` | `string` | **Required**. |


```http
  GET /auth/logout
```

| Headers | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `Authorization` | `Bearer` | **Required**. |

#### Create Work Order

```http
  POST /api/work-order
```

| Headers | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `Authorization` | `Bearer` | **Required**. Must Production Manager Token|

| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `product_name`      | `string` | **Required**. |
| `quantity`      | `integer` | **Required**. |
| `deadline`      | `date` | **Required**. |
| `assigned_operator_id`      | `integer` | **Required**. operator id |


#### Get Operator

```http
  GET /api/operators
```

| Headers | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `Authorization` | `Bearer` | **Required**. Must Production Manager Token|

#### Create Work Order Progress

```http
  POST /api/progress/work-order
```

| Headers | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `Authorization` | `Bearer` | **Required**. Must Operator Token|

| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `work_order_id`      | `integer` | **Required**. Id of work order|
| `status`      | `string` | **Required**. new status|
| `quantity`      | `date` | **Required**. quantity status|

#### Get Work Order

```http
  GET /api/work-order
```

| Headers | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `Authorization` | `Bearer` | **Required**. |


#### Update Work Order

```http
  PATCH /api/work-order/{work_order_id}
```

| Headers | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `Authorization` | `Bearer` | **Required**.|

| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `status`      | `string` | **sometimes**. new status|
| `assigned_operator_id`      | `date` | **sometimes**. operator id|


## Tech Stack

**Framework:** Laravel

**Database:** MySQL

