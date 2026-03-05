package com.sekolah.exambro

import android.content.Intent
import android.os.Bundle
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import com.sekolah.exambro.databinding.ActivityConfigBinding
import com.sekolah.exambro.utils.PreferenceManager

/**
 * ConfigActivity – administrator setup screen.
 *
 * The administrator (teacher/proctor) sets:
 *  - Server URL (e.g. http://192.168.1.10 or https://sekolah.sch.id)
 *  - Admin exit PIN (4-6 digits) used to leave kiosk mode
 *
 * These values are persisted in SharedPreferences and are only accessible
 * again by knowing the current PIN.
 */
class ConfigActivity : AppCompatActivity() {

    private lateinit var binding: ActivityConfigBinding
    private lateinit var prefs: PreferenceManager

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityConfigBinding.inflate(layoutInflater)
        setContentView(binding.root)

        supportActionBar?.title = getString(R.string.config_title)

        prefs = PreferenceManager(this)

        // Pre-fill with saved values (if reconfiguring)
        binding.etServerUrl.setText(prefs.getServerUrl())

        binding.btnSaveConfig.setOnClickListener {
            saveConfig()
        }
    }

    private fun saveConfig() {
        val serverUrl = binding.etServerUrl.text.toString().trim()
        val pin = binding.etAdminPin.text.toString().trim()
        val pinConfirm = binding.etAdminPinConfirm.text.toString().trim()

        // Validate server URL
        if (serverUrl.isBlank()) {
            binding.tilServerUrl.error = getString(R.string.error_server_url_empty)
            return
        }
        if (!serverUrl.startsWith("http://") && !serverUrl.startsWith("https://")) {
            binding.tilServerUrl.error = getString(R.string.error_server_url_format)
            return
        }
        binding.tilServerUrl.error = null

        // Validate PIN (only required on first setup; skip if PIN already set and fields are blank)
        val existingPin = prefs.getAdminPin()
        if (existingPin.isBlank() || pin.isNotBlank() || pinConfirm.isNotBlank()) {
            if (pin.length < 4) {
                binding.tilAdminPin.error = getString(R.string.error_pin_length)
                return
            }
            if (pin != pinConfirm) {
                binding.tilAdminPinConfirm.error = getString(R.string.error_pin_mismatch)
                return
            }
            binding.tilAdminPin.error = null
            binding.tilAdminPinConfirm.error = null
            prefs.setAdminPin(pin)
        }

        // Normalize: remove trailing slash
        val normalizedUrl = serverUrl.trimEnd('/')
        prefs.setServerUrl(normalizedUrl)

        Toast.makeText(this, getString(R.string.config_saved), Toast.LENGTH_SHORT).show()

        startActivity(Intent(this, LoginActivity::class.java))
        finish()
    }
}
