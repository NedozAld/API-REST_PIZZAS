# 🧪 PRUEBAS DEL API - MÓDULO 1: AUTENTICACIÓN
# LA PIZZERÍA - CRAZY SNAKES

# Este script contiene ejemplos de prueba del API de autenticación
# Compatible con PowerShell en Windows

# ============================================
# CONFIGURACIÓN
# ============================================

$baseUrl = "http://localhost:8000"
$apiUrl = "$baseUrl/api/auth"

# Usuarios de prueba
$adminEmail = "admin@lapizzeria.ec"
$adminPassword = "Admin@123456"

# ============================================
# FUNCIONES HELPER
# ============================================

function Invoke-ApiRequest {
    param(
        [string]$Method,
        [string]$Endpoint,
        [object]$Body,
        [string]$Token
    )
    
    $url = "$apiUrl$Endpoint"
    $headers = @{
        "Content-Type" = "application/json"
        "Accept" = "application/json"
    }
    
    if ($Token) {
        $headers["Authorization"] = "Bearer $Token"
    }
    
    try {
        $bodyJson = $Body | ConvertTo-Json
        $response = Invoke-WebRequest -Uri $url -Method $Method -Body $bodyJson -Headers $headers -ErrorAction Stop
        return $response.Content | ConvertFrom-Json
    }
    catch {
        Write-Error "Error en solicitud: $_"
        if ($_.Exception.Response) {
            $streamReader = [System.IO.StreamReader]::new($_.Exception.Response.GetResponseStream())
            $errorContent = $streamReader.ReadToEnd()
            Write-Host "Error Response: $errorContent"
        }
        return $null
    }
}

# ============================================
# TEST 1: LOGIN
# ============================================

Write-Host "`n=== TEST 1: LOGIN ===" -ForegroundColor Green

$loginData = @{
    email = $adminEmail
    password = $adminPassword
}

$loginResponse = Invoke-ApiRequest -Method "POST" -Endpoint "/login" -Body $loginData

if ($loginResponse.exito) {
    Write-Host "✅ Login exitoso!" -ForegroundColor Green
    Write-Host "Mensaje: $($loginResponse.mensaje)"
    Write-Host "Usuario: $($loginResponse.usuario.nombre)"
    Write-Host "Token: $($loginResponse.token.Substring(0, 30))..."
    
    # Guardar token para siguientes pruebas
    $token = $loginResponse.token
}
else {
    Write-Host "❌ Error en login: $($loginResponse.mensaje)" -ForegroundColor Red
    exit
}

# ============================================
# TEST 2: OBTENER USUARIO AUTENTICADO
# ============================================

Write-Host "`n=== TEST 2: GET ME ===" -ForegroundColor Green

$meResponse = Invoke-ApiRequest -Method "GET" -Endpoint "/me" -Token $token

if ($meResponse.exito) {
    Write-Host "✅ Datos del usuario obtenidos!" -ForegroundColor Green
    Write-Host "ID: $($meResponse.usuario.id)"
    Write-Host "Nombre: $($meResponse.usuario.nombre)"
    Write-Host "Email: $($meResponse.usuario.email)"
    Write-Host "Rol: $($meResponse.rol.nombre)"
}
else {
    Write-Host "❌ Error: $($meResponse.mensaje)" -ForegroundColor Red
}

# ============================================
# TEST 3: VERIFICAR TOKEN
# ============================================

Write-Host "`n=== TEST 3: VERIFY TOKEN ===" -ForegroundColor Green

$verifyResponse = Invoke-ApiRequest -Method "GET" -Endpoint "/verify-token" -Token $token

if ($verifyResponse.exito) {
    Write-Host "✅ Token válido!" -ForegroundColor Green
    Write-Host "Usuario ID: $($verifyResponse.usuario_id)"
}
else {
    Write-Host "❌ Token inválido: $($verifyResponse.mensaje)" -ForegroundColor Red
}

# ============================================
# TEST 4: REGISTRO DE NUEVO USUARIO
# ============================================

Write-Host "`n=== TEST 4: REGISTER ===" -ForegroundColor Green

$timestamp = Get-Date -Format "yyyyMMddHHmmss"
$registerData = @{
    nombre = "Usuario Test $timestamp"
    email = "test$timestamp@lapizzeria.ec"
    telefono = "+593998765432"
    password = "TestPass@123"
    password_confirmation = "TestPass@123"
}

$registerResponse = Invoke-ApiRequest -Method "POST" -Endpoint "/register" -Body $registerData

if ($registerResponse.exito) {
    Write-Host "✅ Usuario registrado exitosamente!" -ForegroundColor Green
    Write-Host "ID: $($registerResponse.usuario.id)"
    Write-Host "Email: $($registerResponse.usuario.email)"
}
else {
    Write-Host "⚠️ Error en registro: $($registerResponse.mensaje)" -ForegroundColor Yellow
}

# ============================================
# TEST 5: CAMBIAR CONTRASEÑA
# ============================================

Write-Host "`n=== TEST 5: CHANGE PASSWORD ===" -ForegroundColor Green

$changePasswordData = @{
    password_actual = $adminPassword
    password_nueva = "NewAdminPass@789"
    password_nueva_confirmation = "NewAdminPass@789"
}

$changeResponse = Invoke-ApiRequest -Method "POST" -Endpoint "/change-password" -Body $changePasswordData -Token $token

if ($changeResponse.exito) {
    Write-Host "✅ Contraseña cambiada exitosamente!" -ForegroundColor Green
    Write-Host "Mensaje: $($changeResponse.mensaje)"
    # Restaurar contraseña para otros tests
    $restoreData = @{
        password_actual = "NewAdminPass@789"
        password_nueva = $adminPassword
        password_nueva_confirmation = $adminPassword
    }
    Invoke-ApiRequest -Method "POST" -Endpoint "/change-password" -Body $restoreData -Token $token | Out-Null
}
else {
    Write-Host "❌ Error: $($changeResponse.mensaje)" -ForegroundColor Red
}

# ============================================
# TEST 6: LOGOUT
# ============================================

Write-Host "`n=== TEST 6: LOGOUT ===" -ForegroundColor Green

$logoutResponse = Invoke-ApiRequest -Method "POST" -Endpoint "/logout" -Token $token

if ($logoutResponse.exito) {
    Write-Host "✅ Logout exitoso!" -ForegroundColor Green
    Write-Host "Mensaje: $($logoutResponse.mensaje)"
}
else {
    Write-Host "❌ Error: $($logoutResponse.mensaje)" -ForegroundColor Red
}

# ============================================
# TEST 7: VERIFICAR TOKEN DESPUÉS DE LOGOUT
# ============================================

Write-Host "`n=== TEST 7: VERIFY TOKEN AFTER LOGOUT ===" -ForegroundColor Green

$verifyAfterLogout = Invoke-ApiRequest -Method "GET" -Endpoint "/verify-token" -Token $token

if (-not $verifyAfterLogout.exito) {
    Write-Host "✅ Token invalidado correctamente!" -ForegroundColor Green
    Write-Host "Mensaje: $($verifyAfterLogout.mensaje)"
}
else {
    Write-Host "⚠️ Warning: Token aún válido después de logout" -ForegroundColor Yellow
}

# ============================================
# RESUMEN
# ============================================

Write-Host "`n=== RESUMEN ===" -ForegroundColor Cyan
Write-Host "✅ Pruebas completadas"
Write-Host "Base URL: $baseUrl"
Write-Host "API URL: $apiUrl"
Write-Host "`nDocumentación: Consulta API_AUTHENTICATION.md"
Write-Host "Colección Postman: authentication-api.postman_collection.json"
