<?php require_once __DIR__.'/php/auth/auth.php'; ?>
<!doctype html>
<html lang="ru">
  <head>
    <?php
      $webSiteTitle=TEXTS["TITLE_SIGN_IN"][LANG];
      require_once "head.php";
    ?>
  </head>
  <body>
      <main>
        <div class="row w-100">
          <div class="col-4"></div>
          <div class="col-4" style="padding-top: 8%;">
            <img src="assets/img/silo_round_OK.png" width="128" height="128" style="display: block; margin-left: auto; margin-right: auto; margin-bottom: 3%;">
            <div class="" style="padding-left: 30%; padding-right: 30%;">
              <form>
                <div class="mb-3">
                  <label for="exampleInputEmail1" class="form-label">
                    <!-- Пользователь -->
                    <?php echo TEXTS["SIGN_IN_USER"][LANG]; ?>
                  </label>
                  <input type="email" class="form-control" id="sign-in-user-name" aria-describedby="emailHelp">
                </div>
                <div class="mb-3">
                  <label for="exampleInputPassword1" class="form-label">
                    <!-- Пароль -->
                    <?php echo TEXTS["SIGN_IN_PASSWORD"][LANG]; ?>
                  </label>
                  <input type="password" class="form-control" id="sign-in-password">
                </div>
                <div class="mb-3 form-check">
                  <input type="checkbox" class="form-check-input" id="sign-in-remember-me">
                  <label class="form-check-label" for="exampleCheck1">
                    <!-- Запомнить меня -->
                    <?php echo TEXTS["SIGN_IN_REMEMBER_ME"][LANG]; ?>
                  </label>
                </div>
                <button type="submit" class="btn btn-primary" id="sign-in-btn-sign-in">
                  <!-- Войти -->
                  <?php echo TEXTS["SIGN_IN_SIGN_IN"][LANG]; ?>
                </button>
                <a href="signup.php" style="display:flex; float: right; margin-top: 7px;">
                  <!-- Зарегистрироваться -->
                  <?php echo TEXTS["SIGN_IN_SIGN_UP"][LANG]; ?>
                </a>
              </form>
              <p class="mt-2 w-100" style="text-align: center;">
                <a href="index.php">
                  <!-- На главную -->
                  <?php echo TEXTS["SIGN_IN_INDEX"][LANG]; ?>
                </a>
              </p>
            </div>
          </div>
          <div class="col-4"></div>
        </div>
      </main>
  </body>
</html>