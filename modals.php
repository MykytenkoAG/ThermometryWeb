<div class="modal fade" id="modal-sign-in" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #4046ff;">
                <h5 class="modal-title" id="" style="color: #ffffff;">Вход</h5>
                <button type="button" id="modal-sign-in-btn-close" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="margin-left: auto; margin-right: auto;">
                <table>
                    <tr>
                        <td style="padding-right: 10px;">Пользователь:</td>
                        <td id="modal-sign-in-user-name"></td>
                    </tr>
                    <tr>
                        <td>Пароль:</td>
                        <td>
                            <input type="password" id="modal-sign-in-password" value="" onchange="" class="form-control mx-auto" style="width: 200px;" value=""></input>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <div style="margin: auto;">
                    <button type="button" id="modal-sign-in-btn-ok" class="btn btn-primary" data-bs-dismiss="modal" style="width: 100px;">Войти</button>
                    <button type="button" id="modal-sign-in-btn-cancel" class="btn btn-secondary" data-bs-dismiss="modal" style="width: 100px;">Отмена</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-pass-change" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #4046ff;">
                <h5 class="modal-title" id="" style="color: #ffffff;">Изменение пароля</h5>
                <button type="button" id="modal-sign-in-btn-close" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="margin-left: auto; margin-right: auto;">
                <table>
                    <tr>
                        <td style="padding-right: 10px;">Новый пароль:</td>
                        <td>
                            <input type="password" id="modal-pass-change-pwd1" value="" onchange="" class="form-control mx-auto" style="width: 200px;" value=""></input>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-right: 10px;">Подтверждение:</td>
                        <td>
                            <input type="password" id="modal-pass-change-pwd2" value="" onchange="" class="form-control mx-auto" style="width: 200px;" value=""></input>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <div style="margin: auto;">
                    <button type="button" id="modal-pass-change-btn-ok" class="btn btn-primary" data-bs-dismiss="modal" style="width: 100px;">OK</button>
                    <button type="button" id="modal-pass-change-btn-cancel" class="btn btn-secondary" data-bs-dismiss="modal" style="width: 100px;">Отмена</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-info" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header" id="modal-info-header" style="background-color: #4046ff;">
            <h5 class="modal-title" id="modal-info-title" style="color: #FFFFFF;"></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body\"><h5 id="modal-info-body-message" style="margin: 10px; text-align: center;"></h5></div>
        <div class="modal-footer">
            <div style="margin: auto;">
                <button type="button" class="btn btn-primary" id="modal-info-btn-ok" data-bs-dismiss="modal" style="width: 150px;">OK</button>
            </div>
        </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-are-you-sure" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #4046ff;">
                <h5 class="modal-title" id=""></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="text-align: center;"><h4 id="modal-are-you-sure-text"></h4></div>
            <div class="modal-footer">
                <div style="margin: auto;">
                    <button type="button" id="modal-are-you-sure-btn-ok" class="btn btn-primary" data-bs-dismiss="modal" style="width: 100px;"></button>
                    <button type="button" id="modal-are-you-sure-btn-cancel" class="btn btn-secondary" data-bs-dismiss="modal" style="width: 100px;"></button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-silo-config-page-change" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
        <div class="modal-header" style="background-color: #520007;">
            <h5 class="modal-title" id="" style="color: white;">Внимание</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body"><h5 id="modal-silo-config-page-change-message"></h5></div>
        <div class="modal-footer">
            <div style="margin: auto;">
                <button type="button" class="btn btn-danger" id="modal-silo-config-page-change-btn-ok" data-bs-dismiss="modal">Все равно перейти</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
            </div>
        </div>
    </div>
    </div>
</div>

<div class="modal fade" id="modal-ts-connection-settings" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #4046ff;">
                <h5 class="modal-title" id="" style="color: #ffffff;">Настройки подключения к ПО Термосервер</h5>
                <button type="button" id="modal-ts-connection-settings-btn-close" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body col" style="margin-left: auto; margin-right: auto; text-align:center;">
                <div class="col-12" style="margin-left: auto; margin-right: auto; text-align:center;">
                    <h6 class="modal-title" id="">Введите IP-адрес и порт термосервера.
                                                  Если программа установлена на этом же компьютере, в качестве IP-адреса введите "127.0.0.1".
                                                  Номер порта был выбран при установке.</h5>
                    <br>
                </div>
                <div class="col-12">
                    <table style="margin-left: auto; margin-right: auto; text-align:center;">
                        <tr>
                            <td style="padding-right: 10px;">IP:</td>
                            <td>
                                <input type="text" id="modal-ts-connection-settings-ip" value="" oninput="vSConf_checkIP()" class="form-control mx-auto" style="width: 200px;" value=""></input>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-right: 10px;">Порт:</td>
                            <td>
                                <input type="number" id="modal-ts-connection-settings-port" value="" onchange="" class="form-control mx-auto" style="width: 200px;" value=""></input>
                            </td>
                        </tr>
                    </table>
                </div>

            </div>
            <div class="modal-footer">
                <div style="margin: auto;">
                    <button type="button" id="modal-ts-connection-settings-btn-ok" onclick="vSConf_ts_connection_settings_Save()" class="btn btn-primary" data-bs-dismiss="modal" style="width: 100px;">Сохранить</button>
                    <button type="button" id="modal-ts-connection-settings-btn-cancel" class="btn btn-secondary" data-bs-dismiss="modal" style="width: 100px;">Отмена</button>
                </div>
            </div>
        </div>
    </div>
</div>
