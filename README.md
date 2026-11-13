# Media Rental API (Laravel)

API REST para gestionar medios publicitarios (vallas, pantallas, mupis, muros), clientes y reservas de alquiler.

## Requisitos

- Docker Desktop (incluye Docker Compose v2)
- Git

No necesitas PHP, Composer, Node ni MySQL instalados: todo corre en contenedores.

## Instalación

El frontend y el backend son dos repositorios. Clónalos **uno al lado del otro**:

```bash
mkdir prueba-practica && cd prueba-practica
git clone https://github.com/IvanReyesl7/media-rental-api.git backend
git clone https://github.com/IvanReyesl7/media-rental-frontend.git frontend
cd backend
cp .env.example .env
```

## Variables de entorno

Se configuran en `backend/.env` (ver `.env.example`):

| Variable | Valor por defecto | Descripción |
|---|---|---|
| `APP_KEY` | (se genera) | Clave de la aplicación (`php artisan key:generate`) |
| `DB_CONNECTION` | `mysql` | Driver de base de datos |
| `DB_HOST` | `db` | Nombre del servicio MySQL en Docker Compose |
| `DB_PORT` | `3306` | Puerto de MySQL |
| `DB_DATABASE` | `app` | Nombre de la base de datos |
| `DB_USERNAME` | `app` | Usuario |
| `DB_PASSWORD` | `secret` | Contraseña |

Estos valores deben coincidir con los de `docker-compose.yml` (servicio `db`).

## Comandos para correr el proyecto

Desde la carpeta `backend/`:

```bash
# 1. Construir e iniciar backend, frontend y base de datos
docker compose up --build

# 2. En otra terminal (esperar ~30 s a que MySQL inicie la primera vez)
docker compose exec backend php artisan key:generate
docker compose exec backend php artisan migrate --seed

# 3. Ejecutar los tests
docker compose exec backend php artisan test

# Detener todo (conserva la base de datos)
docker compose down

# Detener y borrar la base de datos
docker compose down -v
```

- API: http://localhost:8000/api
- Frontend: http://localhost:3000

`migrate --seed` crea las tablas y carga 14 medios de ejemplo y un cliente demo.

## Endpoints

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/media` | Lista paginada. Filtros: `type`, `status`, `search`, `per_page`, `page` |
| POST | `/api/media` | Crear medio |
| GET | `/api/media/{id}` | Detalle |
| PUT | `/api/media/{id}` | Actualizar |
| DELETE | `/api/media/{id}` | Eliminar (409 si tiene reservas) |
| GET | `/api/media/{id}/availability?starts_at=YYYY-MM-DD&ends_at=YYYY-MM-DD` | Disponibilidad y conflictos |
| GET | `/api/customers` | Listar clientes |
| POST | `/api/customers` | Crear cliente |
| GET | `/api/customers/{id}` | Detalle |
| GET | `/api/bookings` | Lista paginada. Filtros: `status`, `media_id`, `customer_id`, `from`, `to` |
| POST | `/api/bookings` | Crear reserva |
| GET | `/api/bookings/{id}` | Detalle |
| PATCH | `/api/bookings/{id}/status` | Cambiar estado (`pending`, `confirmed`, `cancelled`) |

Enviar siempre el encabezado `Accept: application/json`.

## Decisiones técnicas

- **Docker Compose con un contenedor por servicio** (backend, frontend, MySQL). Los servicios se comunican por nombre (`DB_HOST=db`) y el código se monta como volumen para desarrollo con recarga en vivo.
- **Form Requests** (`MediaStoreRequest`, `MediaUpdateRequest`, `CustomerStoreRequest`, `BookingStoreRequest`, `BookingStatusUpdateRequest`) para que los controladores solo coordinen y la validación quede en un solo lugar.
- **API Resources** (`MediaResource`, `CustomerResource`, `BookingResource`) para tener respuestas consistentes: precios siempre numéricos, sin campos internos, y relaciones solo cuando se cargan (`whenLoaded`).
- **El precio total lo calcula el servidor**: `días × price_per_day`, contando ambos extremos como días (del 1 al 3 son 3 días). El cliente nunca envía el precio.
- **Regla de solapamiento**: una reserva choca con otra del mismo medio si `inicio_existente <= fin_nuevo` y `fin_existente >= inicio_nuevo`. Las reservas canceladas no bloquean fechas, y un medio inactivo no se puede reservar.
- **Integridad referencial**: llaves foráneas con `restrictOnDelete()`. Un medio o cliente con reservas no se puede borrar; el controlador responde 409 con un mensaje claro en vez de un error 500.
- **Paginación y filtros** con `paginate()` + `withQueryString()`, para que los enlaces conserven los filtros. La búsqueda aplica a nombre y ubicación.
- **Tests de feature** (`tests/Feature/BookingTest.php`) para el cálculo de precio, el solapamiento, la cancelación y las validaciones. Usan SQLite en memoria, sin tocar los datos de MySQL.