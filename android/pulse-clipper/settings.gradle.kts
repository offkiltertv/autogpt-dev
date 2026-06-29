pluginManagement {
    repositories {
        google()
        mavenCentral()
        gradlePluginPortal()
    }
}

dependencyResolutionManagement {
    repositoriesMode.set(RepositoriesMode.FAIL_ON_PROJECT_REPOS)
    repositories {
        google()
        mavenCentral()
    }
}

rootProject.name = "pulse-clipper"

// M0 scaffold modules. UI modules (:app, :app-tv, :design) are added in M1/M2.
include(":core")
include(":capture")
include(":sources")
include(":editor")
include(":upload")
