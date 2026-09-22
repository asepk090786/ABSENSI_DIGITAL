package com.sekolah.exambro.utils

import android.content.Context
import android.content.SharedPreferences

/**
 * PreferenceManager – thin wrapper around [SharedPreferences] for all
 * persistent settings used by ExamBro Client.
 *
 * Keys are kept private to this class to avoid typo-based bugs elsewhere.
 */
class PreferenceManager(context: Context) {

    private val prefs: SharedPreferences =
        context.getSharedPreferences(PREF_FILE_NAME, Context.MODE_PRIVATE)

    // -------------------------------------------------------------------------
    // Server URL
    // -------------------------------------------------------------------------

    /** The base URL of the Absensi Digital server (e.g. https://sekolah.sch.id). */
    fun getServerUrl(): String = prefs.getString(KEY_SERVER_URL, "") ?: ""

    fun setServerUrl(url: String) {
        prefs.edit().putString(KEY_SERVER_URL, url).apply()
    }

    // -------------------------------------------------------------------------
    // Admin PIN
    // -------------------------------------------------------------------------

    /** The admin PIN used to exit kiosk mode. */
    fun getAdminPin(): String = prefs.getString(KEY_ADMIN_PIN, "") ?: ""

    fun setAdminPin(pin: String) {
        prefs.edit().putString(KEY_ADMIN_PIN, pin).apply()
    }

    // -------------------------------------------------------------------------
    // School name (optional branding)
    // -------------------------------------------------------------------------

    fun getSchoolName(): String = prefs.getString(KEY_SCHOOL_NAME, "") ?: ""

    fun setSchoolName(name: String) {
        prefs.edit().putString(KEY_SCHOOL_NAME, name).apply()
    }

    // -------------------------------------------------------------------------
    // Constants
    // -------------------------------------------------------------------------

    companion object {
        private const val PREF_FILE_NAME = "exambro_prefs"
        private const val KEY_SERVER_URL = "server_url"
        private const val KEY_ADMIN_PIN = "admin_pin"
        private const val KEY_SCHOOL_NAME = "school_name"
    }
}
