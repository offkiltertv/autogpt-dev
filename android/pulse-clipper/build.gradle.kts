// Root build file — OFFKILTER Pulse Clipper (M0 scaffold).
// Plugin versions are declared here and applied (apply false) per-module.
plugins {
    id("com.android.library") version "8.6.0" apply false
    id("org.jetbrains.kotlin.android") version "2.0.20" apply false
    id("org.jetbrains.kotlin.jvm") version "2.0.20" apply false
}

// Shared SDK constants referenced by module build files.
extra["compileSdk"] = 34
extra["minSdk"] = 33      // Android 13
extra["targetSdk"] = 34   // Android 14 (MediaProjection FGS rules)
