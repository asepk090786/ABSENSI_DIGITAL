package com.sekolah.exambro

import android.content.Intent
import android.os.Bundle
import android.view.Menu
import android.view.MenuItem
import android.view.View
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import com.sekolah.exambro.databinding.ActivityLoginBinding
import com.sekolah.exambro.utils.PreferenceManager

/**
 * LoginActivity – student login screen.
 *
 * The student enters their NIS (Nomor Induk Siswa) and password.
 * The app then navigates to [ExamActivity] with the server URL so that the
 * student can authenticate through the standard Laravel web login page inside
 * the locked WebView.
 *
 * No REST API call is made here – authentication happens entirely inside the
 * WebView, which preserves session cookies automatically.
 */
class LoginActivity : AppCompatActivity() {

    private lateinit var binding: ActivityLoginBinding
    private lateinit var prefs: PreferenceManager

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityLoginBinding.inflate(layoutInflater)
        setContentView(binding.root)

        supportActionBar?.title = getString(R.string.login_title)

        prefs = PreferenceManager(this)

        binding.tvServerUrl.text = getString(R.string.label_server, prefs.getServerUrl())

        binding.btnStartExam.setOnClickListener {
            openExamBrowser()
        }
    }

    override fun onCreateOptionsMenu(menu: Menu): Boolean {
        menuInflater.inflate(R.menu.menu_login, menu)
        return true
    }

    override fun onOptionsItemSelected(item: MenuItem): Boolean {
        return when (item.itemId) {
            R.id.action_settings -> {
                startActivity(Intent(this, ConfigActivity::class.java))
                true
            }
            else -> super.onOptionsItemSelected(item)
        }
    }

    private fun openExamBrowser() {
        val serverUrl = prefs.getServerUrl()
        if (serverUrl.isBlank()) {
            Toast.makeText(this, getString(R.string.error_server_not_configured), Toast.LENGTH_LONG).show()
            startActivity(Intent(this, ConfigActivity::class.java))
            return
        }

        val intent = Intent(this, ExamActivity::class.java).apply {
            putExtra(ExamActivity.EXTRA_URL, serverUrl)
        }
        startActivity(intent)
    }
}
