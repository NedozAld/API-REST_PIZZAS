<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Http\Requests\SetupTwoFactorRequest;
use App\Http\Requests\VerifyTwoFactorRequest;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Writer;

class TwoFactorAuthController extends Controller
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * POST /api/auth/2fa/setup
     * US-090: Generar código QR y secret key para 2FA
     * 
     * Respuesta:
     * {
     *   "exito": true,
     *   "datos": {
     *     "secret": "JBSWY3DPEBLW64TMMQ...",
     *     "qr_code": "<svg>...</svg>",
     *     "mensaje": "Escanea el código QR con Google Authenticator"
     *   }
     * }
     */
    public function setup(SetupTwoFactorRequest $request)
    {
        try {
            $usuario = Auth::guard('sanctum')->user();

            // Si ya tiene 2FA habilitado, no puede generar otro
            if ($usuario->tieneDosFa()) {
                return response()->json([
                    'exito' => false,
                    'mensaje' => '2FA ya está habilitado. Deshabilitalo primero.'
                ], 422);
            }

            // Generar secret key
            $secret = $this->google2fa->generateSecretKey();

            // Generar QR Code en formato SVG
            $qrCode = $this->generateQrCode($secret, $usuario->email);

            return response()->json([
                'exito' => true,
                'datos' => [
                    'secret' => $secret,
                    'qr_code' => $qrCode,
                    'mensaje' => 'Escanea el código QR con Google Authenticator, Microsoft Authenticator o Authy'
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Error al generar código QR: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/auth/2fa/verify
     * US-090: Verificar código 2FA y habilitar
     * 
     * Request:
     * {
     *   "secret": "JBSWY3DPEBLW64TMMQ...",
     *   "codigo": "123456"
     * }
     * 
     * Respuesta:
     * {
     *   "exito": true,
     *   "datos": {
     *     "message": "2FA habilitado exitosamente",
     *     "backup_codes": ["XXXXXX", "YYYYYY", ...]
     *   }
     * }
     */
    public function verify(VerifyTwoFactorRequest $request)
    {
        try {
            $usuario = Auth::guard('sanctum')->user();
            $secret = $request->secret;
            $codigo = $request->codigo;

            // Validar que el código sea válido
            $valid = $this->google2fa->verifyKey($secret, $codigo, 2); // Tolerancia de 2 ventanas
            
            if (!$valid) {
                return response()->json([
                    'exito' => false,
                    'mensaje' => 'Código 2FA inválido. Verifica el código e intenta nuevamente.'
                ], 422);
            }

            // Generar backup codes (códigos de recuperación)
            $backupCodes = $this->generateBackupCodes();

            // Guardar secret y backup codes
            $usuario->update([
                'dos_fa_secret' => $secret,
                'dos_fa_habilitado' => true,
                'dos_fa_backup_codes' => $backupCodes
            ]);

            return response()->json([
                'exito' => true,
                'datos' => [
                    'mensaje' => '2FA habilitado exitosamente',
                    'backup_codes' => $backupCodes,
                    'instrucciones' => 'Guarda estos códigos de recuperación en un lugar seguro. Los necesitarás si pierdes acceso a tu autenticador.'
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Error al verificar código: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/auth/2fa/disable
     * US-090: Deshabilitar 2FA
     * 
     * Request:
     * {
     *   "codigo": "123456"  // Código actual del autenticador
     * }
     */
    public function disable(VerifyTwoFactorRequest $request)
    {
        try {
            $usuario = Auth::guard('sanctum')->user();

            if (!$usuario->tieneDosFa()) {
                return response()->json([
                    'exito' => false,
                    'mensaje' => '2FA no está habilitado'
                ], 422);
            }

            // Validar código actual
            $codigo = $request->codigo;
            $valid = $this->google2fa->verifyKey($usuario->dos_fa_secret, $codigo, 2);

            if (!$valid) {
                return response()->json([
                    'exito' => false,
                    'mensaje' => 'Código 2FA inválido. No se puede deshabilitar 2FA.'
                ], 422);
            }

            // Deshabilitar 2FA
            $usuario->update([
                'dos_fa_habilitado' => false,
                'dos_fa_secret' => null,
                'dos_fa_backup_codes' => null
            ]);

            return response()->json([
                'exito' => true,
                'mensaje' => '2FA deshabilitado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Error al deshabilitar 2FA: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/auth/2fa/verify-login
     * Endpoint para verificar código 2FA durante login
     * 
     * Request:
     * {
     *   "email": "usuario@example.com",
     *   "codigo": "123456"
     * }
     * 
     * Respuesta: Token Sanctum si es válido
     */
    public function verifyLogin(\Illuminate\Http\Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'codigo' => 'required|digits:6'
            ]);

            $usuario = Usuario::where('email', $request->email)->first();

            if (!$usuario || !$usuario->tieneDosFa()) {
                return response()->json([
                    'exito' => false,
                    'mensaje' => '2FA no habilitado para este usuario'
                ], 422);
            }

            // Verificar código
            $valid = $this->google2fa->verifyKey($usuario->dos_fa_secret, $request->codigo, 2);
            
            if (!$valid) {
                // Verificar si es un backup code
                $backupCodes = $usuario->dos_fa_backup_codes ?? [];
                if (in_array($request->codigo, $backupCodes)) {
                    // Usar el backup code (eliminarlo de la lista)
                    $backupCodes = array_diff($backupCodes, [$request->codigo]);
                    $usuario->update(['dos_fa_backup_codes' => array_values($backupCodes)]);
                } else {
                    return response()->json([
                        'exito' => false,
                        'mensaje' => 'Código 2FA inválido'
                    ], 422);
                }
            }

            // Código válido - crear token
            $token = $usuario->createToken('2fa-verified')->plainTextToken;

            return response()->json([
                'exito' => true,
                'datos' => [
                    'token' => $token,
                    'usuario' => [
                        'id' => $usuario->id,
                        'nombre' => $usuario->nombre,
                        'email' => $usuario->email,
                        'dos_fa_habilitado' => $usuario->dos_fa_habilitado
                    ]
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar QR Code en formato SVG
     */
    private function generateQrCode(string $secret, string $email): string
    {
        try {
            $appName = config('app.name', 'Pizzería API');
            $qrCodeUrl = $this->google2fa->getQRCodeUrl($appName, $email, $secret);

            // Usar bacon/bacon-qr-code para generar SVG
            $renderer = new ImageRenderer(
                new SvgImageBackEnd()
            );
            $writer = new Writer($renderer);
            $qrCode = $writer->writeString($qrCodeUrl);

            return $qrCode;
        } catch (\Exception $e) {
            // Fallback: retornar URL
            return $this->google2fa->getQRCodeUrl(config('app.name', 'Pizzería API'), $email, $secret);
        }
    }

    /**
     * Generar códigos de recuperación (backup codes)
     */
    private function generateBackupCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6));
        }
        return $codes;
    }
}
