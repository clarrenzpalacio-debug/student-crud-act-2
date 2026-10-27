# Final - Lab Activity 2: Presentation & Video Script

## 🎙️ Video Presentation Script

### 1. Introduction
> "Good day! In this video, I will demonstrate our implementation for Final Lab Activity 2: Token-Based Authentication and Role-Based Access Control (RBAC) using Laravel Sanctum, extending our RESTful API from Activity 1."

---

### 2. What Changed from Lab Activity 1 to Lab Activity 2
> "In **Lab Activity 1**, all API endpoints were public. Anyone could view, create, edit, or delete student records without logging in.
>
> In **Lab Activity 2**, we implemented complete security and access control:
> 1. **Laravel Sanctum Integration:** Tokens are generated upon login and required for requests.
> 2. **User Roles:** Added a `role` column to the `users` table to distinguish between Administrator (`admin`) and Regular User (`user`).
> 3. **Authentication Endpoints:** Added `/api/login`, `/api/register`, and `/api/logout`.
> 4. **Role Middleware:** Created `RoleMiddleware` (`role:admin`) to guard administrative actions.
> 5. **Restricted Permissions:** Regular users have read-only access (`GET`), while only Admins can create (`POST`), update (`PUT`), and delete (`DELETE`)."

---

### 3. Postman Demonstration (Follow This Order)

#### Step 1: Start Server & Test Unauthenticated Request (401 Unauthorized)
- **Action:** Send `GET http://127.0.0.1:8000/api/students` without any token.
- **Script:**
  > "First, we run the Laravel development server. When sending a GET request to `/api/students` without an authorization header, the API rejects it with a `401 Unauthorized` status code because valid bearer authentication is required."

#### Step 2: Login as Regular User & Access Read-Only Endpoint
- **Action:** Send `POST http://127.0.0.1:8000/api/login`
  - Body (raw JSON):
    ```json
    {
      "email": "user@example.com",
      "password": "password"
    }
    ```
- **Script:**
  > "Next, we authenticate using regular user credentials. The server responds with a 200 OK and an API token. We copy this token into Postman's Authorization tab as a Bearer Token. Sending a GET request to `/api/students` now succeeds with a 200 OK response."

#### Step 3: Regular User Forbidden from Admin Actions (403 Forbidden)
- **Action:** Send `POST http://127.0.0.1:8000/api/students` using the Regular User token:
  - Body (raw JSON):
    ```json
    {
      "Student_id": "2024-0001",
      "first_name": "Juan",
      "last_name": "Dela Cruz",
      "email": "juan@example.com",
      "course": "BSIT",
      "year_level": 3
    }
    ```
- **Script:**
  > "Now, if this regular user attempts to create a new student record via POST, the request is intercepted by our role middleware and returns `403 Forbidden` with the message: 'Forbidden. You do not have the required permissions.' The user cannot modify or delete records either."

#### Step 4: Login as Administrator & Perform Admin Actions (201 Created)
- **Action:** Send `POST http://127.0.0.1:8000/api/login`
  - Body (raw JSON):
    ```json
    {
      "email": "admin@example.com",
      "password": "password"
    }
    ```
- **Script:**
  > "Now, we log in as the Administrator using `admin@example.com` and password `password`. We update the Bearer Token in Postman with the admin token. Re-sending the POST request to create a student now succeeds with `201 Created`."

#### Step 5: Logout and Token Revocation (Test Revoked Token)
- **Action 1:** Send `POST http://127.0.0.1:8000/api/logout` with the Bearer Token.
- **Action 2:** Immediately send `GET http://127.0.0.1:8000/api/students` with that same token.
- **Script:**
  > "Lastly, we test the logout endpoint by sending POST `/api/logout`. This revokes and deletes the current access token. If we attempt to access `/api/students` again using this same token, it immediately returns `401 Unauthorized`."

---

### 4. Code Structure Walkthrough
> "Here is a brief look at the project codebase:
> - `routes/api.php`: Configures the public login/register routes and wraps protected routes inside `auth:sanctum` and `role:admin`.
> - `app/Http/Controllers/Api/AuthController.php`: Handles user login, issuing personal access tokens, and token deletion upon logout.
> - `app/Http/Middleware/RoleMiddleware.php`: Verifies that the authenticated user possesses the required role before proceeding.
> - `app/Models/User.php`: Uses Laravel Sanctum's `HasApiTokens` trait and includes the `role` attribute.
> - `database/seeders/DatabaseSeeder.php`: Pre-seeds both an Admin account (`admin@example.com`) and a Regular User account (`user@example.com`) with password `password`."

---

## 📋 Summary: How It Affects Postman

| Request / Action | Activity 1 Behavior | Activity 2 Behavior |
| :--- | :--- | :--- |
| **`GET /api/students` (No token)** | Returns `200 OK` (Public) | Returns **`401 Unauthorized`** |
| **`POST /api/login`** | Did not exist | Returns Bearer Token & User profile |
| **Postman Headers** | `Accept: application/json` | `Accept: application/json` + `Authorization: Bearer <token>` |
| **`POST /api/students` (Regular User)** | Allowed (`201 Created`) | Returns **`403 Forbidden`** |
| **`POST /api/students` (Admin User)** | Allowed (`201 Created`) | Allowed (**`201 Created`**) |
| **`POST /api/logout`** | Did not exist | Revokes token; subsequent calls give **`401 Unauthorized`** |

