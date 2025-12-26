#!/bin/bash

# Script para ejecutar tests completos de la Pizzería API
# Uso: ./run-tests.sh [opción]

set -e

APP_NAME="Pizzeria API"
TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')

echo "=========================================="
echo "$APP_NAME - Test Suite"
echo "Ejecutado: $TIMESTAMP"
echo "=========================================="
echo ""

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Función para imprimir estado
print_status() {
    if [ $1 -eq 0 ]; then
        echo -e "${GREEN}✓ $2${NC}"
    else
        echo -e "${RED}✗ $2${NC}"
        exit 1
    fi
}

# Parsear argumentos
OPTION=${1:-all}

case $OPTION in
    all)
        echo -e "${YELLOW}Ejecutando TODOS los tests...${NC}"
        php artisan test --parallel
        print_status $? "Todos los tests pasaron"
        ;;
    
    auth)
        echo -e "${YELLOW}Ejecutando tests de Authentication...${NC}"
        php artisan test tests/Feature/Auth/AuthenticationTest.php --verbose
        print_status $? "Tests de Auth completados"
        ;;
    
    productos)
        echo -e "${YELLOW}Ejecutando tests de Productos...${NC}"
        php artisan test tests/Feature/Productos/ProductoTest.php --verbose
        print_status $? "Tests de Productos completados"
        ;;
    
    pedidos)
        echo -e "${YELLOW}Ejecutando tests de Pedidos...${NC}"
        php artisan test tests/Feature/Pedidos/PedidoTest.php --verbose
        print_status $? "Tests de Pedidos completados"
        ;;
    
    coverage)
        echo -e "${YELLOW}Ejecutando tests con cobertura de código...${NC}"
        php artisan test --coverage --coverage-html=coverage
        print_status $? "Reporte de cobertura generado en coverage/index.html"
        ;;
    
    fast)
        echo -e "${YELLOW}Ejecutando tests en paralelo (rápido)...${NC}"
        php artisan test --parallel --no-coverage
        print_status $? "Tests rápidos completados"
        ;;
    
    *)
        echo -e "${YELLOW}Uso: ./run-tests.sh [opción]${NC}"
        echo ""
        echo "Opciones disponibles:"
        echo "  all       - Ejecutar todos los tests (default)"
        echo "  auth      - Tests de Autenticación"
        echo "  productos - Tests de Productos"
        echo "  pedidos   - Tests de Pedidos"
        echo "  coverage  - Tests con reporte de cobertura"
        echo "  fast      - Tests en paralelo (rápido)"
        echo ""
        echo "Ejemplos:"
        echo "  ./run-tests.sh auth"
        echo "  ./run-tests.sh coverage"
        exit 1
        ;;
esac

echo ""
echo "=========================================="
echo -e "${GREEN}Test Suite completado exitosamente${NC}"
echo "=========================================="
