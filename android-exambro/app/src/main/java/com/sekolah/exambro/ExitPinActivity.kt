package com.sekolah.exambro

import android.os.Bundle
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import com.sekolah.exambro.databinding.ActivityExitPinBinding
import com.sekolah.exambro.utils.PreferenceManager

/**
 * ExitPinActivity – modal PIN-entry screen for leaving kiosk mode.
 *
 * The proctor/administrator enters the 4-6 digit PIN that was configured in
 * [ConfigActivity].  On success, RESULT_OK is returned to [ExamActivity]
 * which then clears the session and navigates back to [LoginActivity].
 *
 * Three incorrect attempts lock out further tries for 30 seconds.
 */
class ExitPinActivity : AppCompatActivity() {

    private lateinit var binding: ActivityExitPinBinding
    private lateinit var prefs: PreferenceManager

    private var failedAttempts = 0
    private var lockedUntilMs = 0L

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityExitPinBinding.inflate(layoutInflater)
        setContentView(binding.root)

        supportActionBar?.title = getString(R.string.exit_pin_title)

        prefs = PreferenceManager(this)

        binding.btnConfirmPin.setOnClickListener {
            verifyPin()
        }

        binding.btnCancel.setOnClickListener {
            setResult(RESULT_CANCELED)
            finish()
        }
    }

    private fun verifyPin() {
        val now = System.currentTimeMillis()
        if (now < lockedUntilMs) {
            val secsLeft = ((lockedUntilMs - now) / 1000).toInt()
            Toast.makeText(
                this,
                getString(R.string.error_pin_locked, secsLeft),
                Toast.LENGTH_SHORT
            ).show()
            return
        }

        val enteredPin = binding.etPin.text.toString()
        val savedPin = prefs.getAdminPin()

        if (enteredPin == savedPin) {
            setResult(RESULT_OK)
            finish()
        } else {
            failedAttempts++
            binding.etPin.text?.clear()

            if (failedAttempts >= MAX_FAILED_ATTEMPTS) {
                lockedUntilMs = System.currentTimeMillis() + LOCKOUT_DURATION_MS
                failedAttempts = 0
                Toast.makeText(
                    this,
                    getString(R.string.error_pin_too_many, LOCKOUT_DURATION_MS / 1000),
                    Toast.LENGTH_LONG
                ).show()
            } else {
                val remaining = MAX_FAILED_ATTEMPTS - failedAttempts
                Toast.makeText(
                    this,
                    getString(R.string.error_pin_wrong, remaining),
                    Toast.LENGTH_SHORT
                ).show()
            }
        }
    }

    companion object {
        private const val MAX_FAILED_ATTEMPTS = 3
        private const val LOCKOUT_DURATION_MS = 30_000L
    }
}
