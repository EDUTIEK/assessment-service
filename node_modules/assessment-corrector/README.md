# assessment-corrector

Web app to Correct an Exam.
Used by the [assessment-service](https://github.com/EDUTIEK/assessment-service).
A build of this web app is committed there and changed with new versions.

This repository is part of the [EDUTIEK project](https://www.edutiek.de). At the moment we don't recommend its use
outside the project. The REST API may change at any time if neded by the project.


## Project Setup

```sh
npm clean-install --ignore-scripts
```

### Compile and Hot-Reload for Development

```sh
npm run dev
```

### Compile and Minify for Production

```sh
npm run build
```

### Open the application

When the app is opened from `index.html` without cookies from the assessment backend, `backendUrl` and `returnUrl` are read from `config.json` next to `index.html`. Cookies named `xlasBackendUrl` and `xlasReturnUrl` override those values.

`public/config.json` holds the default values. It is committed and copied to `dist/config.json` on build. In an installation, replace that file without rebuilding.

For development, create `dev.config.json` in the project root. The dev server serves it instead of `public/config.json`. It is not committed, so local values stay when the default changes. Restart `npm run dev` after adding the file.
