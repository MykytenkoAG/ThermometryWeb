<?php

function createModalSignIn($modalID, $userNameInTable, $user, $inputPassID){

    $outStr = "
    <div class=\"modal fade\" id=\"$modalID\" data-bs-backdrop=\"static\" data-bs-keyboard=\"false\" tabindex=\"-1\" aria-labelledby=\"staticBackdropLabel\" aria-hidden=\"true\">
        <div class=\"modal-dialog modal-dialog-centered\">
            <div class=\"modal-content\">
                <div class=\"modal-header\" style=\"background-color: #4046ff;\">
                    <h5 class=\"modal-title\" id=\"\" style=\"color: #ffffff;\">Вход</h5>
                    <button type=\"button\" class=\"btn-close\" onclick=\"modalClearInput('$inputPassID')\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>
                </div>
                <div class=\"modal-body\" style=\"margin-left: auto; margin-right: auto;\">
                    <table>
                        <tr>
                            <td style=\"padding-right: 10px;\">Пользователь:</td>
                            <td>$userNameInTable</td>
                        </tr>
                        <tr>
                            <td>Пароль:</td>
                            <td>
                                <input type=\"password\" id=\"$inputPassID\" value=\"\" onchange=\"\" class=\"form-control mx-auto\" style=\"width: 200px;\" value=\"\"></input>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class=\"modal-footer\">
                    <div style=\"margin: auto;\">
                        <button type=\"button\" id=\"silo-config-save-changes-modal-ok-button\" class=\"btn btn-primary\" data-bs-dismiss=\"modal\"
                                style=\"width: 100px;\" onclick=\"authSignIn('$user', '$inputPassID')\">Войти</button>
                        <button type=\"button\" class=\"btn btn-secondary\" onclick=\"modalClearInput('$inputPassID')\" data-bs-dismiss=\"modal\" style=\"width: 100px;\">Отмена</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    ";

    return $outStr;
}

echo createModalSignIn("modal-sign-in-oper", "Оператор", "oper", "modal-sign-in-password-oper");
echo createModalSignIn("modal-sign-in-tehn", "Технолог", "tehn", "modal-sign-in-password-tehn");

?>

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
