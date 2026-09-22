package com.sekolah.exambro

import android.annotation.SuppressLint
import android.app.ActivityManager
import android.content.Context
import android.content.Intent
import android.graphics.Bitmap
import android.net.ConnectivityManager
import android.net.NetworkCapabilities
import android.os.Bundle
import android.view.KeyEvent
import android.view.View
import android.view.WindowManager
import android.webkit.CookieManager
import android.webkit.WebResourceError
import android.webkit.WebResourceRequest
import android.webkit.WebSettings
import android.webkit.WebView
import android.webkit.WebViewClient
import android.widget.Toast
import androidx.activity.OnBackPressedCallback
import androidx.activity.result.contract.ActivityResultContracts
import androidx.appcompat.app.AppCompatActivity
import com.sekolah.exambro.BuildConfig
import com.sekolah.exambro.databinding.ActivityExamBinding
import com.sekolah.exambro.utils.KioskModeHelper
import com.sekolah.exambro.utils.PreferenceManager

/**
 * ExamActivity – the main secure exam browser.
 *
 * This activity runs in kiosk (lock-task) mode so students cannot:
 *  - Switch to other apps
 *  - Use the Home or Recents button
 *  - Take screenshots
 *  - Exit without knowing the admin PIN
 *
 * The exam content is served from the school's Absensi Digital server
 * (Laravel web app) loaded inside a [WebView].
 */
class ExamActivity : AppCompatActivity() {

    private lateinit var binding: ActivityExamBinding
    private lateinit var prefs: PreferenceManager
    private lateinit var kioskHelper: KioskModeHelper

    private var examUrl: String = ""

    /** Launcher for [ExitPinActivity]; exits the exam on RESULT_OK. */
    private val exitPinLauncher = registerForActivityResult(
        ActivityResultContracts.StartActivityForResult()
    ) { result ->
        if (result.resultCode == RESULT_OK) {
            exitExam()
        }
    }

    // -------------------------------------------------------------------------
    // Lifecycle
    // -------------------------------------------------------------------------

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        // Prevent screenshots / screen recording during the exam
        window.addFlags(WindowManager.LayoutParams.FLAG_SECURE)

        binding = ActivityExamBinding.inflate(layoutInflater)
        setContentView(binding.root)

        // Hide the action bar for an immersive full-screen experience
        supportActionBar?.hide()

        prefs = PreferenceManager(this)
        kioskHelper = KioskModeHelper(this)

        examUrl = intent.getStringExtra(EXTRA_URL) ?: prefs.getServerUrl()

        setupWebView()
        setupButtons()
        setupBackPressHandler()
        enterKioskMode()

        loadExam()
    }

    override fun onResume() {
        super.onResume()
        // Re-enter immersive mode if the user somehow left it
        kioskHelper.enableImmersiveMode(window)
    }

    override fun onWindowFocusChanged(hasFocus: Boolean) {
        super.onWindowFocusChanged(hasFocus)
        if (hasFocus) {
            kioskHelper.enableImmersiveMode(window)
        }
    }

    override fun onStop() {
        super.onStop()
        // If the activity loses focus during an exam, try to bring it back
        if (!isFinishing) {
            val bringToFront = Intent(this, ExamActivity::class.java).apply {
                addFlags(Intent.FLAG_ACTIVITY_REORDER_TO_FRONT)
            }
            startActivity(bringToFront)
        }
    }

    override fun onDestroy() {
        kioskHelper.stopLockTask(this)
        binding.webView.destroy()
        super.onDestroy()
    }

    // -------------------------------------------------------------------------
    // Key handling – disable hardware back/volume during exam
    // -------------------------------------------------------------------------

    override fun onKeyDown(keyCode: Int, event: KeyEvent?): Boolean {
        return when (keyCode) {
            KeyEvent.KEYCODE_BACK,
            KeyEvent.KEYCODE_HOME,
            KeyEvent.KEYCODE_APP_SWITCH,
            KeyEvent.KEYCODE_VOLUME_UP,
            KeyEvent.KEYCODE_VOLUME_DOWN -> true   // consumed / blocked
            else -> super.onKeyDown(keyCode, event)
        }
    }

    // -------------------------------------------------------------------------
    // Setup helpers
    // -------------------------------------------------------------------------

    @SuppressLint("SetJavaScriptEnabled")
    private fun setupWebView() {
        val webView = binding.webView

        webView.settings.apply {
            javaScriptEnabled = true
            domStorageEnabled = true
            databaseEnabled = true
            allowFileAccess = false
            allowContentAccess = false
            setSupportZoom(true)
            builtInZoomControls = true
            displayZoomControls = false
            useWideViewPort = true
            loadWithOverviewMode = true
            cacheMode = WebSettings.LOAD_DEFAULT
            userAgentString = "${userAgentString} ExamBroClient/${BuildConfig.VERSION_NAME}"
        }

        // Accept cookies (required for Laravel session)
        CookieManager.getInstance().apply {
            setAcceptCookie(true)
            setAcceptThirdPartyCookies(webView, true)
        }

        webView.webViewClient = object : WebViewClient() {
            override fun onPageStarted(view: WebView?, url: String?, favicon: Bitmap?) {
                super.onPageStarted(view, url, favicon)
                showLoading(true)
            }

            override fun onPageFinished(view: WebView?, url: String?) {
                super.onPageFinished(view, url)
                showLoading(false)
                CookieManager.getInstance().flush()
            }

            override fun onReceivedError(
                view: WebView?,
                request: WebResourceRequest?,
                error: WebResourceError?
            ) {
                super.onReceivedError(view, request, error)
                if (request?.isForMainFrame == true) {
                    showLoading(false)
                    showError(error?.description?.toString() ?: getString(R.string.error_network))
                }
            }

            // Ensure all navigation stays inside this WebView (no external browser)
            override fun shouldOverrideUrlLoading(
                view: WebView?,
                request: WebResourceRequest?
            ): Boolean {
                val url = request?.url?.toString() ?: return false
                return if (url.startsWith("http://") || url.startsWith("https://")) {
                    view?.loadUrl(url)
                    true
                } else {
                    true // Block non-HTTP(S) schemes
                }
            }
        }
    }

    private fun setupButtons() {
        // Exit button shows admin PIN dialog
        binding.btnExit.setOnClickListener {
            promptAdminExit()
        }

        // Refresh button reloads the current page
        binding.btnRefresh.setOnClickListener {
            binding.webView.reload()
        }

        // Error retry button
        binding.btnRetry.setOnClickListener {
            hideError()
            loadExam()
        }
    }

    private fun setupBackPressHandler() {
        onBackPressedDispatcher.addCallback(this, object : OnBackPressedCallback(true) {
            override fun handleOnBackPressed() {
                // Back press inside WebView navigates back in history
                if (binding.webView.canGoBack()) {
                    binding.webView.goBack()
                }
                // Else: silently consumed – students cannot leave the app
            }
        })
    }

    private fun enterKioskMode() {
        kioskHelper.startLockTask(this)
        kioskHelper.enableImmersiveMode(window)
    }

    private fun loadExam() {
        if (!isNetworkAvailable()) {
            showError(getString(R.string.error_no_internet))
            return
        }
        binding.webView.loadUrl(examUrl)
    }

    // -------------------------------------------------------------------------
    // Admin exit flow
    // -------------------------------------------------------------------------

    private fun promptAdminExit() {
        exitPinLauncher.launch(Intent(this, ExitPinActivity::class.java))
    }

    private fun exitExam() {
        // Clear session cookies so the next student starts fresh
        CookieManager.getInstance().removeAllCookies(null)
        CookieManager.getInstance().flush()

        kioskHelper.stopLockTask(this)

        startActivity(Intent(this, LoginActivity::class.java).apply {
            flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TASK
        })
        finish()
    }

    // -------------------------------------------------------------------------
    // UI helpers
    // -------------------------------------------------------------------------

    private fun showLoading(visible: Boolean) {
        binding.progressBar.visibility = if (visible) View.VISIBLE else View.GONE
    }

    private fun showError(message: String) {
        binding.layoutError.visibility = View.VISIBLE
        binding.tvErrorMessage.text = message
    }

    private fun hideError() {
        binding.layoutError.visibility = View.GONE
    }

    private fun isNetworkAvailable(): Boolean {
        val cm = getSystemService(Context.CONNECTIVITY_SERVICE) as ConnectivityManager
        val network = cm.activeNetwork ?: return false
        val caps = cm.getNetworkCapabilities(network) ?: return false
        return caps.hasCapability(NetworkCapabilities.NET_CAPABILITY_INTERNET)
    }

    // -------------------------------------------------------------------------
    // Constants
    // -------------------------------------------------------------------------

    companion object {
        const val EXTRA_URL = "extra_url"
    }
}
