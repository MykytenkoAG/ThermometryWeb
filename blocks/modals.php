<div class="modal fade" id="modal-wrong-password" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #520007;">
                <h5 class="modal-title" id="staticBackdropLabel" style="color: #FFFFFF;">Пароль не верный</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-footer">
                <div style="margin: auto;">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-auth-oper" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="staticBackdropLabel">Авторизоваться как оператор</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <input type="password" id="modal-pass-oper" onchange="" class="form-control mx-auto" style="width: 300px;" value=""></input>
        </div>
        <div class="modal-footer">
            <div style="margin: auto;">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="auth_oper()">OK</button>
            </div>
        </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-auth-tehn" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="staticBackdropLabel">Авторизоваться как технолог</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <input type="password" id="modal-pass-tehn" onchange="" class="form-control mx-auto" style="width: 300px;" value=""></input>
        </div>
        <div class="modal-footer">
            <div style="margin: auto;">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="auth_tehn()">OK</button>
            </div>
        </div>
        </div>
    </div>
</div>

<div class="modal fade" id="dbg-main-modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Debug</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body\"><h5 id="dbg-modal-body-message"></h5></div>
            <div class="modal-footer">
                <div style="margin: auto;">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal fade" id="ind-lvl-auto-all-silo-enable" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Автоматическое определение уровня</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Установить автоопределение уровня на всех силосах?
            </div>
            <div class="modal-footer">
                <div style="margin: auto;">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="enable_all_auto_lvl_mode()">Да</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                </div>
            </div>
          </div>
        </div>
      </div>

<div class="modal fade" id="silo-config-page-change-modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered">
  <div class="modal-content">
    <div class="modal-header" style="background-color: #520007;">
        <h5 class="modal-title" id="" style="color: white;">Внимание</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body"><h5 id="silo-config-page-change-modal-message"></h5></div>
    <div class="modal-footer">
        <div style="margin: auto;">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal"
                    onclick="tbl_prodtypes_changed=0;tbl_prodtypesbysilo_changed=0;onPageChange(curr_url_ind);">Все равно перейти</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
        </div>
    </div>
  </div>
</div>
</div>

<div class="modal fade" id="silo-config-successfull-changes-in-db-modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered">
  <div class="modal-content">
    <div class="modal-header" style="background-color: #4046ff;">
        <h5 class="modal-title" id=""></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body"><h5 id="">Изменения успешно внесены в Базу данных</h5></div>
    <div class="modal-footer">
        <div style="margin: auto;">
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">ОК</button>
        </div>
    </div>
  </div>
</div>
</div>

<div class="modal fade" id="silo-config-save-changes-modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #4046ff;">
                <h5 class="modal-title" id=""></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"><h5 id="">Вы уверены?</h5></div>
            <div class="modal-footer">
                <div style="margin: auto;">
                    <button type="button" id="silo-config-save-changes-modal-ok-button" class="btn btn-primary" data-bs-dismiss="modal">Да</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Нет</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php

?>