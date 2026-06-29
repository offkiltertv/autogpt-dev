// :core — pure-Kotlin domain models and contracts. No Android framework deps,
// so it builds and unit-tests fast and stays UI-agnostic.
plugins {
    id("org.jetbrains.kotlin.jvm")
}

dependencies {
    implementation("org.jetbrains.kotlinx:kotlinx-coroutines-core:1.8.1")
    testImplementation("org.jetbrains.kotlin:kotlin-test:2.0.20")
}
