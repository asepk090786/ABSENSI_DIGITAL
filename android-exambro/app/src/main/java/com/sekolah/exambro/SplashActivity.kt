package com.sekolah.exambro

import android.content.Intent
import android.os.Bundle
import android.os.Handler
import android.os.Looper
import androidx.appcompat.app.AppCompatActivity
import com.sekolah.exambro.databinding.ActivitySplashBinding
import com.sekolah.exambro.utils.PreferenceManager

/**
 * SplashActivity – launch screen shown briefly when the app starts.
 *
 * After a short delay it decides where to navigate:
 *  - If the server URL has never been configured → [ConfigActivity]
 *  - Otherwise → [LoginActivity]
 */
class SplashActivity : AppCompatActivity() {

    private lateinit var binding: ActivitySplashBinding

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivitySplashBinding.inflate(layoutInflater)
        setContentView(binding.root)

        // Hide the action bar on the splash screen
        supportActionBar?.hide()

        val prefs = PreferenceManager(this)

        Handler(Looper.getMainLooper()).postDelayed({
            val destination = if (prefs.getServerUrl().isBlank()) {
                Intent(this, ConfigActivity::class.java)
            } else {
                Intent(this, LoginActivity::class.java)
            }
            startActivity(destination)
            finish()
        }, SPLASH_DELAY_MS)
    }

    companion object {
        private const val SPLASH_DELAY_MS = 1_500L
    }
}
