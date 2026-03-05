package com.sekolah.exambro.utils

import android.app.Activity
import android.app.ActivityManager
import android.content.Context
import android.os.Build
import android.view.View
import android.view.Window
import android.view.WindowInsets
import android.view.WindowInsetsController

/**
 * KioskModeHelper – wraps Android's Lock Task API and immersive-mode flags.
 *
 * Lock Task mode prevents the user from leaving the app via the Home,
 * Recents or Back hardware keys.  It requires the device to be enrolled in a
 * Device Policy Controller (DPC) or the app to be whitelisted by Android
 * Device Owner policy for full kiosk enforcement.  On non-DPC devices the app
 * falls back to pinning (screen-pin mode) which still prevents most
 * navigation while showing an exit hint overlay.
 */
class KioskModeHelper(private val context: Context) {

    /**
     * Attempt to start lock-task / screen-pin mode.
     * Should be called from [Activity.onCreate].
     */
    fun startLockTask(activity: Activity) {
        try {
            activity.startLockTask()
        } catch (e: IllegalStateException) {
            // Device is not enrolled; screen pinning is not available in all modes.
            // The app still runs but hardware navigation may not be fully blocked.
        }
    }

    /**
     * Stop lock-task mode when the exam session ends.
     * Should be called before navigating away from [ExamActivity].
     */
    fun stopLockTask(activity: Activity) {
        try {
            val am = context.getSystemService(Context.ACTIVITY_SERVICE) as ActivityManager
            if (am.lockTaskModeState != ActivityManager.LOCK_TASK_MODE_NONE) {
                activity.stopLockTask()
            }
        } catch (e: Exception) {
            // Ignore – already stopped or not supported
        }
    }

    /**
     * Enable full-screen immersive (sticky) mode so that the status bar and
     * navigation bar are hidden.
     */
    fun enableImmersiveMode(window: Window) {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.R) {
            window.insetsController?.let { controller ->
                controller.hide(WindowInsets.Type.statusBars() or WindowInsets.Type.navigationBars())
                controller.systemBarsBehavior =
                    WindowInsetsController.BEHAVIOR_SHOW_TRANSIENT_BARS_BY_SWIPE
            }
        } else {
            @Suppress("DEPRECATION")
            window.decorView.systemUiVisibility = (
                View.SYSTEM_UI_FLAG_IMMERSIVE_STICKY
                    or View.SYSTEM_UI_FLAG_LAYOUT_STABLE
                    or View.SYSTEM_UI_FLAG_LAYOUT_HIDE_NAVIGATION
                    or View.SYSTEM_UI_FLAG_LAYOUT_FULLSCREEN
                    or View.SYSTEM_UI_FLAG_HIDE_NAVIGATION
                    or View.SYSTEM_UI_FLAG_FULLSCREEN
            )
        }
    }
}
