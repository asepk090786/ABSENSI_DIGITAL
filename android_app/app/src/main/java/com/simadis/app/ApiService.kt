package com.simadis.app

import retrofit2.Call
import retrofit2.http.Body
import retrofit2.http.POST

interface ApiService {
    @POST("auth/login")
    fun login(@Body request: LoginRequest): Call<LoginResponse>

    @POST("auth/logout")
    fun logout(): Call<LogoutResponse>
}

data class LoginRequest(
    val login: String,
    val password: String
)

data class LoginResponse(
    val message: String,
    val token: String? = null,
    val user: UserPayload? = null
)

data class LogoutResponse(
    val message: String
)

data class UserPayload(
    val id: Int,
    val name: String,
    val username: String,
    val email: String,
    val role: String? = null,
    val roles: List<String> = emptyList()
)
