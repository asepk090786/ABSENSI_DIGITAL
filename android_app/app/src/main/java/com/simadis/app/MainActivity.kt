package com.simadis.app

import android.os.Bundle
import android.view.View
import android.widget.Button
import android.widget.EditText
import android.widget.LinearLayout
import android.widget.TextView
import androidx.appcompat.app.AppCompatActivity
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

class MainActivity : AppCompatActivity() {
    private lateinit var loginLayout: LinearLayout
    private lateinit var dashboardLayout: LinearLayout
    private lateinit var loginInput: EditText
    private lateinit var passwordInput: EditText
    private lateinit var resultText: TextView
    private lateinit var statusText: TextView
    private lateinit var logoutButton: Button
    private lateinit var sessionManager: SessionManager

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_main)

        sessionManager = SessionManager(this)

        loginLayout = findViewById(R.id.loginLayout)
        dashboardLayout = findViewById(R.id.dashboardLayout)
        loginInput = findViewById(R.id.loginInput)
        passwordInput = findViewById(R.id.passwordInput)
        resultText = findViewById(R.id.resultText)
        statusText = findViewById(R.id.statusText)
        logoutButton = findViewById(R.id.logoutButton)

        findViewById<Button>(R.id.loginButton).setOnClickListener {
            doLogin()
        }

        logoutButton.setOnClickListener {
            doLogout()
        }

        if (sessionManager.getToken() != null) {
            statusText.text = "Status: sesi tersimpan"
            showDashboardScreen()
        } else {
            showLoginScreen()
        }
    }

    private fun doLogin() {
        val login = loginInput.text.toString().trim()
        val password = passwordInput.text.toString().trim()

        if (login.isEmpty() || password.isEmpty()) {
            resultText.text = "Isi username/email dan password"
            return
        }

        resultText.text = "Sedang login..."

        RetrofitClient.apiService.login(LoginRequest(login, password)).enqueue(object : Callback<LoginResponse> {
            override fun onResponse(call: Call<LoginResponse>, response: Response<LoginResponse>) {
                if (response.isSuccessful && response.body() != null) {
                    val body = response.body()!!
                    body.token?.let { sessionManager.saveToken(it) }
                    resultText.text = body.message
                    statusText.text = "Status: login berhasil"
                    showDashboardScreen()
                } else {
                    resultText.text = "Login gagal: ${response.code()}"
                    statusText.text = "Status: gagal login"
                }
            }

            override fun onFailure(call: Call<LoginResponse>, t: Throwable) {
                resultText.text = "Error: ${t.message}"
                statusText.text = "Status: tidak bisa terhubung"
            }
        })
    }

    private fun doLogout() {
        resultText.text = "Sedang logout..."

        RetrofitClient.apiService.logout().enqueue(object : Callback<LogoutResponse> {
            override fun onResponse(call: Call<LogoutResponse>, response: Response<LogoutResponse>) {
                sessionManager.clearToken()
                resultText.text = "Logout berhasil"
                statusText.text = "Status: belum login"
                showLoginScreen()
            }

            override fun onFailure(call: Call<LogoutResponse>, t: Throwable) {
                resultText.text = "Logout gagal: ${t.message}"
            }
        })
    }

    private fun showLoginScreen() {
        loginLayout.visibility = View.VISIBLE
        dashboardLayout.visibility = View.GONE
    }

    private fun showDashboardScreen() {
        loginLayout.visibility = View.GONE
        dashboardLayout.visibility = View.VISIBLE
    }
}
