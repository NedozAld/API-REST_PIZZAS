# Guía de Integración: Módulo 4 Continuación (Frontend)

**Módulo:** Pedidos (Continuación) + Múltiples Direcciones  
**Endpoints:** 11 nuevas rutas  
**Tecnologías:** JavaScript/Vue/React  

---

## 📌 Configuración Base

### Headers Requeridos
```javascript
const headers = {
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'Authorization': `Bearer ${token}` // Token Sanctum del usuario
};
```

### Base URL
```javascript
const API_URL = 'http://localhost:8000/api';
```

### Instancia Axios (Recomendado)
```javascript
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

// Interceptor para agregar token
api.interceptors.request.use(config => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export default api;
```

---

## 🏠 Dirección del Cliente - Gestión Completa

### 1️⃣ Listar Direcciones del Cliente

```javascript
// GET /api/clientes/{cliente_id}/direcciones
async function obtenerDirecciones(clienteId) {
  try {
    const response = await api.get(`/clientes/${clienteId}/direcciones`);
    console.log('Direcciones:', response.data.data);
    return response.data.data;
  } catch (error) {
    console.error('Error al obtener direcciones:', error.response.data);
  }
}

// Respuesta exitosa:
{
  "data": [
    {
      "id": 1,
      "cliente_id": 5,
      "nombre_direccion": "Casa",
      "calle": "Carrera 5",
      "numero": "123",
      "apartamento": "4B",
      "ciudad": "Bogotá",
      "codigo_postal": "110111",
      "provincia": "Cundinamarca",
      "referencia": "Frente a la farmacia",
      "favorita": true,
      "activa": true,
      "direccion_completo": "Carrera 5 #123-4B, Bogotá 110111",
      "created_at": "2024-12-29T10:00:00Z"
    }
  ],
  "meta": { "total": 2, "per_page": 15 }
}
```

### 2️⃣ Crear Nueva Dirección

```javascript
// POST /api/clientes/{cliente_id}/direcciones
async function crearDireccion(clienteId, dataDireccion) {
  try {
    const response = await api.post(`/clientes/${clienteId}/direcciones`, {
      nombre_direccion: "Oficina",
      calle: "Calle 45",
      numero: "789",
      apartamento: "",
      ciudad: "Medellín",
      codigo_postal: "050001",
      provincia: "Antioquia",
      referencia: "Edificio azul",
      favorita: false
    });
    console.log('Dirección creada:', response.data.data);
    return response.data.data;
  } catch (error) {
    // Errores de validación
    if (error.response.status === 422) {
      console.error('Errores de validación:', error.response.data.errors);
      // {
      //   "nombre_direccion": ["El campo nombre_direccion es requerido"],
      //   "calle": ["El campo calle es requerido"],
      //   "numero": ["El campo numero es requerido"],
      //   "ciudad": ["El campo ciudad es requerido"],
      //   "codigo_postal": ["El campo codigo_postal es requerido"]
      // }
    }
  }
}
```

### 3️⃣ Obtener Dirección Específica

```javascript
// GET /api/clientes/{cliente_id}/direcciones/{id}
async function obtenerDireccion(clienteId, direccionId) {
  try {
    const response = await api.get(`/clientes/${clienteId}/direcciones/${direccionId}`);
    return response.data.data;
  } catch (error) {
    if (error.response.status === 404) {
      console.error('Dirección no encontrada');
    }
  }
}
```

### 4️⃣ Actualizar Dirección

```javascript
// PUT /api/clientes/{cliente_id}/direcciones/{id}
async function actualizarDireccion(clienteId, direccionId, datosActualizados) {
  try {
    const response = await api.put(
      `/clientes/${clienteId}/direcciones/${direccionId}`,
      {
        nombre_direccion: "Casa (Actualizada)",
        calle: "Carrera 6",
        numero: "456",
        ciudad: "Bogotá",
        codigo_postal: "110112",
        provincia: "Cundinamarca"
      }
    );
    console.log('Dirección actualizada:', response.data.data);
    return response.data.data;
  } catch (error) {
    console.error('Error al actualizar:', error.response.data);
  }
}
```

### 5️⃣ Marcar Como Dirección Favorita

```javascript
// PATCH /api/clientes/{cliente_id}/direcciones/{id}/favorita
async function marcarComeFavorita(clienteId, direccionId) {
  try {
    const response = await api.patch(
      `/clientes/${clienteId}/direcciones/${direccionId}/favorita`
    );
    console.log('Dirección marcada como favorita:', response.data.data);
    return response.data.data;
  } catch (error) {
    if (error.response.status === 404) {
      console.error('Dirección no encontrada');
    }
  }
}

// Nota: Automáticamente desmarca otras direcciones como favorita
```

### 6️⃣ Obtener Dirección Favorita

```javascript
// GET /api/clientes/{cliente_id}/direcciones/favorita/obtener
async function obtenerDireccionFavorita(clienteId) {
  try {
    const response = await api.get(`/clientes/${clienteId}/direcciones/favorita/obtener`);
    return response.data.data;
  } catch (error) {
    if (error.response.status === 404) {
      console.error('No hay dirección favorita asignada');
    }
  }
}

// Respuesta:
{
  "data": {
    "id": 1,
    "cliente_id": 5,
    "nombre_direccion": "Casa",
    "direccion_completo": "Carrera 5 #123-4B, Bogotá 110111",
    "favorita": true
  }
}
```

### 7️⃣ Eliminar Dirección (Soft Delete)

```javascript
// DELETE /api/clientes/{cliente_id}/direcciones/{id}
async function eliminarDireccion(clienteId, direccionId) {
  try {
    const response = await api.delete(`/clientes/${clienteId}/direcciones/${direccionId}`);
    console.log('Dirección eliminada:', response.data.message);
    return response.data.message;
  } catch (error) {
    if (error.response.status === 404) {
      console.error('Dirección no encontrada');
    }
  }
}

// Respuesta:
// "Dirección eliminada correctamente"
```

---

## 📦 Gestión de Pedidos Mejorada

### 1️⃣ Búsqueda Avanzada de Pedidos

```javascript
// GET /api/pedidos/buscar?q=...&estado=...&cliente_id=...
async function buscarPedidos(filtros = {}) {
  try {
    const params = new URLSearchParams();
    
    if (filtros.q) params.append('q', filtros.q); // número o cliente
    if (filtros.estado) params.append('estado', filtros.estado);
    if (filtros.cliente_id) params.append('cliente_id', filtros.cliente_id);
    if (filtros.fecha_desde) params.append('fecha_desde', filtros.fecha_desde);
    if (filtros.fecha_hasta) params.append('fecha_hasta', filtros.fecha_hasta);
    if (filtros.precio_min) params.append('precio_min', filtros.precio_min);
    if (filtros.precio_max) params.append('precio_max', filtros.precio_max);
    if (filtros.page) params.append('page', filtros.page);

    const response = await api.get(`/pedidos/buscar?${params}`);
    return response.data;
  } catch (error) {
    console.error('Error en búsqueda:', error.response.data);
  }
}

// Ejemplos de uso:
// Búsqueda simple
const resultados = await buscarPedidos({ q: 'PED-2024' });

// Búsqueda por estado
const confirmados = await buscarPedidos({ estado: 'CONFIRMADO' });

// Búsqueda por rango de precios
const caros = await buscarPedidos({ 
  precio_min: 100000, 
  precio_max: 500000 
});

// Búsqueda por cliente y rango de fechas
const pedidosCliente = await buscarPedidos({
  cliente_id: 5,
  fecha_desde: '2024-12-01',
  fecha_hasta: '2024-12-31'
});

// Filtros combinados
const filtradoCompleto = await buscarPedidos({
  q: 'Juan García',
  estado: 'ENTREGADO',
  fecha_desde: '2024-12-25',
  precio_min: 50000
});

// Respuesta:
{
  "data": [
    {
      "id": 1,
      "numero_pedido": "PED-2024-001",
      "cliente_id": 5,
      "estado": "CONFIRMADO",
      "subtotal": 250000,
      "impuesto": 47500,
      "costo_envio": 5000,
      "descuento": 0,
      "total": 302500,
      "fecha_creacion": "2024-12-28T10:00:00Z",
      "fecha_entrega": null
    }
  ],
  "meta": {
    "total": 15,
    "per_page": 15,
    "current_page": 1,
    "last_page": 1
  },
  "filtros": {
    "q": "Juan",
    "estado": "CONFIRMADO"
  }
}
```

### 2️⃣ Repetir Pedido Anterior

```javascript
// POST /api/pedidos/repetir/{id}
async function repetirPedido(pedidoId) {
  try {
    const response = await api.post(`/pedidos/repetir/${pedidoId}`);
    console.log('Pedido repetido:', response.data.data);
    
    // El cliente recibe automáticamente una notificación
    // Los stocks se reducen nuevamente
    // Se genera un nuevo número de pedido
    
    return response.data.data;
  } catch (error) {
    if (error.response.status === 404) {
      console.error('Pedido no encontrado');
    } else if (error.response.status === 403) {
      console.error('No puedes repetir este pedido (no te pertenece)');
    } else if (error.response.status === 400) {
      console.error('No hay stock disponible:', error.response.data.message);
    }
  }
}

// Respuesta exitosa:
{
  "data": {
    "id": 45,
    "numero_pedido": "PED-2024-045",
    "cliente_id": 5,
    "estado": "CONFIRMADO",
    "subtotal": 250000,
    "impuesto": 47500,
    "costo_envio": 5000,
    "descuento": 0,
    "total": 302500,
    "notas": null,
    "fecha_entrega": null,
    "created_at": "2024-12-29T11:30:00Z",
    "detalles": [
      {
        "id": 1,
        "pedido_id": 45,
        "producto_id": 3,
        "cantidad": 2,
        "precio_unitario": 50000
      }
    ]
  },
  "message": "Pedido repetido exitosamente"
}
```

### 3️⃣ Marcar Pedido Entregado

```javascript
// PATCH /api/pedidos/{id}/entregado
async function marcarPedidoEntregado(pedidoId, fechaEntrega = null, comentario = null) {
  try {
    const payload = {};
    
    if (fechaEntrega) {
      payload.fecha_entrega = fechaEntrega; // Formato: YYYY-MM-DD
    }
    
    if (comentario) {
      payload.comentario = comentario; // Max 500 caracteres
    }

    const response = await api.patch(`/pedidos/${pedidoId}/entregado`, payload);
    console.log('Pedido marcado como entregado:', response.data.data);
    
    // Se crea automáticamente una notificación al cliente
    
    return response.data.data;
  } catch (error) {
    if (error.response.status === 404) {
      console.error('Pedido no encontrado');
    } else if (error.response.status === 400) {
      console.error('El pedido no está en estado CONFIRMADO:', error.response.data.message);
    }
  }
}

// Ejemplos de uso:
// Marcar entregado hoy
await marcarPedidoEntregado(1);

// Marcar entregado con fecha específica
await marcarPedidoEntregado(1, '2024-12-29');

// Marcar entregado con comentario
await marcarPedidoEntregado(1, '2024-12-29', 'Entregado al portero del edificio');

// Respuesta:
{
  "data": {
    "id": 1,
    "numero_pedido": "PED-2024-001",
    "estado": "ENTREGADO",
    "fecha_entrega": "2024-12-29",
    "total": 302500
  },
  "message": "Pedido marcado como entregado"
}
```

### 4️⃣ Agregar Notas al Pedido

```javascript
// PUT /api/pedidos/{id}/notas
async function agregarNotasAlPedido(pedidoId, notas) {
  try {
    const response = await api.put(`/pedidos/${pedidoId}/notas`, {
      notas: notas // Max 1000 caracteres
    });
    console.log('Notas actualizadas:', response.data.data);
    return response.data.data;
  } catch (error) {
    if (error.response.status === 404) {
      console.error('Pedido no encontrado');
    } else if (error.response.status === 422) {
      console.error('Error de validación:', error.response.data.errors);
      // Las notas no deben exceder 1000 caracteres
    }
  }
}

// Ejemplos:
await agregarNotasAlPedido(1, 'Llamar 15 minutos antes de entregar');
await agregarNotasAlPedido(1, 'Entregar en puerta trasera. Sin pimienta roja.');

// Respuesta:
{
  "data": {
    "id": 1,
    "numero_pedido": "PED-2024-001",
    "notas": "Entregar en puerta trasera. Sin pimienta roja.",
    "updated_at": "2024-12-29T11:45:00Z"
  },
  "message": "Notas actualizadas correctamente"
}
```

---

## 🎨 Ejemplos de Componentes (Vue.js)

### Componente: Gestor de Direcciones

```vue
<template>
  <div class="direcciones-container">
    <h2>Mis Direcciones</h2>
    
    <!-- Formulario crear/editar -->
    <div v-if="mostrarFormulario" class="formulario">
      <input v-model="formData.nombre_direccion" placeholder="Casa, Oficina, etc">
      <input v-model="formData.calle" placeholder="Calle">
      <input v-model="formData.numero" placeholder="Número">
      <input v-model="formData.apartamento" placeholder="Apartamento">
      <input v-model="formData.ciudad" placeholder="Ciudad">
      <input v-model="formData.codigo_postal" placeholder="Código postal">
      <input v-model="formData.provincia" placeholder="Provincia">
      <input v-model="formData.referencia" placeholder="Referencia">
      
      <checkbox v-model="formData.favorita"> Marcar como favorita</checkbox>
      
      <button @click="guardarDireccion">{{ editandoId ? 'Actualizar' : 'Crear' }}</button>
      <button @click="cancelar">Cancelar</button>
    </div>

    <!-- Lista de direcciones -->
    <div v-if="!mostrarFormulario" class="lista-direcciones">
      <div v-for="dir in direcciones" :key="dir.id" class="tarjeta-direccion">
        <h3>{{ dir.nombre_direccion }}</h3>
        <p>{{ dir.direccion_completo }}</p>
        <p v-if="dir.referencia"><small>{{ dir.referencia }}</small></p>
        
        <div class="acciones">
          <button v-if="!dir.favorita" @click="marcarFavorita(dir.id)">★ Favorita</button>
          <span v-else class="favorita">★ Favorita</span>
          
          <button @click="editar(dir)">✎ Editar</button>
          <button @click="eliminar(dir.id)">✕ Eliminar</button>
        </div>
      </div>
    </div>

    <button v-if="!mostrarFormulario" @click="mostrarFormulario = true">
      + Agregar Dirección
    </button>
  </div>
</template>

<script>
import api from '@/services/api';

export default {
  data() {
    return {
      direcciones: [],
      mostrarFormulario: false,
      editandoId: null,
      formData: {
        nombre_direccion: '',
        calle: '',
        numero: '',
        apartamento: '',
        ciudad: '',
        codigo_postal: '',
        provincia: '',
        referencia: '',
        favorita: false
      }
    };
  },
  mounted() {
    this.cargarDirecciones();
  },
  methods: {
    async cargarDirecciones() {
      const clienteId = this.$route.params.clienteId;
      const response = await api.get(`/clientes/${clienteId}/direcciones`);
      this.direcciones = response.data.data;
    },
    async guardarDireccion() {
      const clienteId = this.$route.params.clienteId;
      
      if (this.editandoId) {
        await api.put(`/clientes/${clienteId}/direcciones/${this.editandoId}`, this.formData);
      } else {
        await api.post(`/clientes/${clienteId}/direcciones`, this.formData);
      }
      
      this.resetear();
      await this.cargarDirecciones();
    },
    async marcarFavorita(dirId) {
      const clienteId = this.$route.params.clienteId;
      await api.patch(`/clientes/${clienteId}/direcciones/${dirId}/favorita`);
      await this.cargarDirecciones();
    },
    async eliminar(dirId) {
      if (confirm('¿Eliminar esta dirección?')) {
        const clienteId = this.$route.params.clienteId;
        await api.delete(`/clientes/${clienteId}/direcciones/${dirId}`);
        await this.cargarDirecciones();
      }
    },
    editar(dir) {
      this.editandoId = dir.id;
      this.formData = { ...dir };
      this.mostrarFormulario = true;
    },
    resetear() {
      this.mostrarFormulario = false;
      this.editandoId = null;
      this.formData = {
        nombre_direccion: '',
        calle: '',
        numero: '',
        apartamento: '',
        ciudad: '',
        codigo_postal: '',
        provincia: '',
        referencia: '',
        favorita: false
      };
    },
    cancelar() {
      this.resetear();
    }
  }
};
</script>
```

### Componente: Búsqueda de Pedidos

```vue
<template>
  <div class="busqueda-pedidos">
    <h2>Buscar Pedidos</h2>
    
    <div class="filtros">
      <input v-model="filtros.q" placeholder="Número o cliente" @input="buscar">
      <select v-model="filtros.estado" @change="buscar">
        <option value="">Todos los estados</option>
        <option value="CONFIRMADO">Confirmado</option>
        <option value="ENTREGADO">Entregado</option>
        <option value="PENDIENTE">Pendiente</option>
      </select>
      
      <input v-model="filtros.fecha_desde" type="date" @change="buscar" placeholder="Desde">
      <input v-model="filtros.fecha_hasta" type="date" @change="buscar" placeholder="Hasta">
      
      <input v-model="filtros.precio_min" type="number" placeholder="Precio mín" @input="buscar">
      <input v-model="filtros.precio_max" type="number" placeholder="Precio máx" @input="buscar">
    </div>

    <!-- Resultados -->
    <div class="resultados">
      <div v-for="pedido in resultados" :key="pedido.id" class="pedido-item">
        <h3>{{ pedido.numero_pedido }}</h3>
        <p>Estado: <strong>{{ pedido.estado }}</strong></p>
        <p>Total: <strong>${{ pedido.total.toLocaleString() }}</strong></p>
        <p>Fecha: {{ new Date(pedido.fecha_creacion).toLocaleDateString() }}</p>
        
        <button @click="irAlPedido(pedido.id)">Ver Detalles</button>
        <button @click="repetirPedido(pedido.id)">Repetir Pedido</button>
      </div>
    </div>

    <!-- Paginación -->
    <div v-if="meta.total > meta.per_page" class="paginacion">
      <button 
        v-for="page in meta.last_page" 
        :key="page"
        :class="{ activa: meta.current_page === page }"
        @click="irAPage(page)"
      >
        {{ page }}
      </button>
    </div>
  </div>
</template>

<script>
import api from '@/services/api';

export default {
  data() {
    return {
      filtros: {
        q: '',
        estado: '',
        fecha_desde: '',
        fecha_hasta: '',
        precio_min: '',
        precio_max: ''
      },
      resultados: [],
      meta: {}
    };
  },
  methods: {
    async buscar() {
      const params = new URLSearchParams();
      
      Object.entries(this.filtros).forEach(([key, value]) => {
        if (value) params.append(key, value);
      });

      const response = await api.get(`/pedidos/buscar?${params}`);
      this.resultados = response.data.data;
      this.meta = response.data.meta;
    },
    async repetirPedido(pedidoId) {
      if (confirm('¿Repetir este pedido?')) {
        const response = await api.post(`/pedidos/repetir/${pedidoId}`);
        alert(`Pedido ${response.data.data.numero_pedido} creado exitosamente`);
      }
    },
    irAPage(page) {
      this.filtros.page = page;
      this.buscar();
    },
    irAlPedido(id) {
      this.$router.push(`/pedidos/${id}`);
    }
  },
  mounted() {
    this.buscar();
  }
};
</script>
```

---

## 🔄 Flujo Completo: Del Carrito al Envío

```javascript
// 1. Cliente ve sus direcciones
const direcciones = await obtenerDirecciones(clienteId);

// 2. Selecciona dirección favorita
const dirFavorita = await obtenerDireccionFavorita(clienteId);

// 3. Realiza compra (endpoint existente)
const nuevoPedido = await crearPedido({
  cliente_id: clienteId,
  direccion_id: dirFavorita.id,
  items: [...]
});

// 4. Luego, el admin busca el pedido
const pedidos = await buscarPedidos({ 
  numero_pedido: nuevoPedido.numero_pedido 
});

// 5. Admin agrega notas especiales
await agregarNotasAlPedido(nuevoPedido.id, 'Entregar después de las 5 PM');

// 6. Se entrega el pedido
await marcarPedidoEntregado(nuevoPedido.id, '2024-12-29', 'Entregado al cliente');

// 7. Cliente puede repetir el pedido después
const pedidoRepetido = await repetirPedido(nuevoPedido.id);
```

---

## 📊 Manejo de Errores

```javascript
async function manejarErrores(endpoint, callback) {
  try {
    return await callback();
  } catch (error) {
    if (error.response.status === 401) {
      console.error('No autenticado. Redirigiendo a login...');
      // Redirigir a login
    } else if (error.response.status === 403) {
      console.error('No tienes permiso para esta acción');
    } else if (error.response.status === 404) {
      console.error('Recurso no encontrado');
    } else if (error.response.status === 422) {
      // Errores de validación
      const errores = error.response.data.errors;
      console.error('Errores de validación:', errores);
      // Mostrar errores en formulario
      Object.entries(errores).forEach(([campo, mensajes]) => {
        console.error(`${campo}: ${mensajes[0]}`);
      });
    } else if (error.response.status === 500) {
      console.error('Error del servidor');
    } else {
      console.error('Error desconocido:', error.message);
    }
    throw error;
  }
}
```

---

## 📝 Validaciones Frontend

```javascript
// Validar dirección antes de enviar
function validarDireccion(dirData) {
  const errores = [];
  
  if (!dirData.nombre_direccion?.trim()) {
    errores.push('El nombre de la dirección es requerido');
  }
  if (!dirData.calle?.trim()) {
    errores.push('La calle es requerida');
  }
  if (!dirData.numero?.trim()) {
    errores.push('El número es requerido');
  }
  if (!dirData.ciudad?.trim()) {
    errores.push('La ciudad es requerida');
  }
  if (!dirData.codigo_postal?.trim()) {
    errores.push('El código postal es requerido');
  }
  if (dirData.nombre_direccion?.length > 100) {
    errores.push('El nombre no puede exceder 100 caracteres');
  }
  if (dirData.referencia?.length > 500) {
    errores.push('La referencia no puede exceder 500 caracteres');
  }
  
  return {
    valido: errores.length === 0,
    errores
  };
}
```

---

**Última actualización:** 2024-12-29  
**Versión API:** 1.0  
**Compatibilidad:** Laravel 11 + Vue 3 / React 18+
