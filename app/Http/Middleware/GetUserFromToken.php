<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class GetUserFromToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $isAjax = $request->ajax();

        $login_url = $this->_get_login_url($request);
        try {
            $auth = JWTAuth::parseToken();
        } catch (TokenExpiredException $e) {
            return response()->json([
                'status_code' => -10001,
                'message' => 'token not provided',
                'data' => $login_url,
            ]);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'status_code' => -10001,
                'message' => 'token not provided',
                'data' => $login_url,
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'status_code' => -10001,
                'message' => 'token not provided',
                'data' => $login_url,
            ]);
        }
        if (!$token = $auth->setRequest($request)->getToken()) {
            if ($isAjax) {
                return response()->json([
                    'status_code' => -10001,
                    'message' => 'token not provided',
                    'data' => $login_url,
                ]);
            } else {
                $this->_go_login($request);
            }
        }
        try {
            $user = $auth->authenticate($token);
        } catch (TokenExpiredException $e) {
            if ($isAjax) {
                return response()->json([
                    'status_code' => -10001,
                    'message' => 'token expired',
                    'data' => $login_url,
                ]);
            } else {
                $this->_go_login($request);
            }
        } catch (JWTException $e) {
            if ($isAjax) {
                return response()->json([
                    'status_code' => -10001,
                    'msg' => 'token invalid',
                    'data' => $login_url,
                ]);
            } else {
                $this->_go_login($request);
            }
        }
        if (!$user) {
            if ($isAjax) {
                return response()->json([
                    'status_code' => -10001,
                    'message' => 'user not found',
                    'data' => $login_url,
                ]);
            } else {
                $this->_go_login($request);
            }
        }


        //$this->events->fire('tymon.jwt.valid', $user);
        return $next($request);
    }

    private function _go_login(Request $request)
    {
        dd("未登录");
    }

    private function _get_login_url(Request $request)
    {
        return "";
    }
}
