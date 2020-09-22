{"version":3,"sources":["table.js"],"names":["BX","namespace","Timeman","Component","Schedule","ShiftPlan","Table","options","BaseComponent","apply","this","containerSelector","isSlider","scheduleId","gridId","useEmployeesTimezoneName","addEventHandlers","prototype","__proto__","constructor","addEventHandlersInsideGrid","addCustomEvent","bind","document","querySelector","addEventListener","e","detail","dayCellNodes","i","length","timeCells","querySelectorAll","timeIndex","selectOneByRole","delegate","onShiftplanMenuToggleClick","onAddShiftPlanClick","addShiftplanBtns","selectAllByRole","shiftplanMenuToggles","deleteUserBtns","onDeleteUserClick","useEmployeesTimezone","getCookie","event","popupDeleteUser","close","userId","currentTarget","dataset","PopupWindow","id","autoHide","draggable","bindOptions","forceBindPosition","closeByEsc","closeIcon","top","right","zIndex","titleBar","message","content","replace","userName","buttons","PopupWindowButton","text","className","events","click","Main","gridManager","getInstanceById","tableFade","ajax","runAction","data","then","response","reloadGrid","show","reload","stopPropagation","preventDefault","planMenuPopup","buildPlanMenuPopup","items","buildPlanMenuItems","PopupMenu","create","maxHeight","bindElement","angle","itemDelete","push","util","getRandomString","htmlspecialchars","onclick","form","createFormDataForShiftPlan","onSuccessShiftPlanDeleted","shiftPlan","itemAdd","btn","formWrapper","formData","FormData","inputs","append","name","value","absenceBlock","findParent","tag","title","isDisabled","style","opacity","onSuccessShiftPlanAdded","dispatchCellHtmlRedraw","getEventContainer","CustomEvent","html","cellHtml","dispatchEvent"],"mappings":"CAAC,WAEAA,GAAGC,UAAU,2CACbD,GAAGE,QAAQC,UAAUC,SAASC,UAAUC,MAAQ,SAAUC,GAEzDP,GAAGE,QAAQC,UAAUK,cAAcC,MAAMC,OAAQC,kBAAmB,2CACpED,KAAKE,SAAWL,EAAQK,SACxBF,KAAKG,WAAaN,EAAQM,WAC1BH,KAAKI,OAASP,EAAQO,OACtBJ,KAAKK,yBAA2B,uBAEhCL,KAAKM,oBAENhB,GAAGE,QAAQC,UAAUC,SAASC,UAAUC,MAAMW,WAC7CC,UAAWlB,GAAGE,QAAQC,UAAUK,cAAcS,UAC9CE,YAAanB,GAAGE,QAAQC,UAAUC,SAASC,UAAUC,MACrDU,iBAAkB,WAEjBN,KAAKU,6BACLpB,GAAGqB,eAAe,gBAAiBX,KAAKU,2BAA2BE,KAAKZ,OACxEa,SAASC,cAAc,yCAAyCC,iBAAiB,qCAAsC,SAAUC,GAEhI,IAAKA,EAAEC,OAAOC,aACd,CACC,OAED,IAAK,IAAIC,EAAI,EAAGA,EAAIH,EAAEC,OAAOC,aAAaE,OAAQD,IAClD,CACC,IAAIE,EAAYL,EAAEC,OAAOC,aAAaC,GAAGG,iBAAiB,6BAC1D,GAAID,EAAUD,SAAW,EACzB,CACC,SAED,IAAK,IAAIG,EAAY,EAAGA,EAAYF,EAAUD,OAAQG,IACtD,CACCjC,GAAGsB,KAAKZ,KAAKwB,gBAAgB,wBAAyBH,EAAUE,IAAa,QAASjC,GAAGmC,SAASzB,KAAK0B,2BAA4B1B,OACnIV,GAAGsB,KAAKZ,KAAKwB,gBAAgB,oBAAqBH,EAAUE,IAAa,QAASjC,GAAGmC,SAASzB,KAAK2B,oBAAqB3B,UAGzHY,KAAKZ,MAAO,QAEfU,2BAA4B,WAE3B,IAAIkB,EAAmB5B,KAAK6B,gBAAgB,qBAC5C,IAAK,IAAIV,EAAI,EAAGA,EAAIS,EAAiBR,OAAQD,IAC7C,CACC7B,GAAGsB,KAAKgB,EAAiBT,GAAI,QAAS7B,GAAGmC,SAASzB,KAAK2B,oBAAqB3B,OAG7E,IAAI8B,EAAuB9B,KAAK6B,gBAAgB,yBAChD,IAAK,IAAIV,EAAI,EAAGA,EAAIW,EAAqBV,OAAQD,IACjD,CACC7B,GAAGsB,KAAKkB,EAAqBX,GAAI,QAAS7B,GAAGmC,SAASzB,KAAK0B,2BAA4B1B,OAGxF,IAAI+B,EAAiB/B,KAAK6B,gBAAgB,mBAC1C,IAAK,IAAIV,EAAI,EAAGA,EAAIY,EAAeX,OAAQD,IAC3C,CACC7B,GAAGsB,KAAKmB,EAAeZ,GAAI,QAAS7B,GAAGmC,SAASzB,KAAKgC,kBAAmBhC,SAG1EiC,qBAAsB,WAErB,OAAOjC,KAAKkC,UAAUlC,KAAKK,4BAA8B,KAE1D2B,kBAAmB,SAAUG,GAE5B,GAAInC,KAAKoC,gBACT,CACCpC,KAAKoC,gBAAgBC,QAEtB,IAAIC,EAASH,EAAMI,cAAcC,QAAQF,OACzCtC,KAAKoC,gBAAkB,IAAI9C,GAAGmD,aAC7BC,GAAI,qCAAuCP,EAAMI,cAAcC,QAAQF,OACvEK,SAAU,KACVC,UAAW,KACXC,aAAcC,kBAAmB,OACjCC,WAAY,KACZC,WAAYC,IAAK,OAAQC,MAAO,QAChCC,OAAQ,EACRC,SAAU9D,GAAG+D,QAAQ,8CACrBC,QAAShE,GAAG+D,QAAQ,wCAAwCE,QAAQ,cAAepB,EAAMI,cAAcC,QAAQgB,UAC/GC,SACC,IAAInE,GAAGoE,mBACNC,KAAMrE,GAAG+D,QAAQ,2CACjBO,UAAW,uBACXC,QACCC,MAAO,WAEN9D,KAAKoC,gBAAgBC,SACpBzB,KAAKZ,SAGT,IAAIV,GAAGoE,mBACNC,KAAMrE,GAAG+D,QAAQ,4CACjBO,UAAW,wBACXC,QACCC,MAAO,SAAUxB,GAEhBtC,KAAKoC,gBAAgBC,QACrB/C,GAAGyE,KAAKC,YAAYC,gBAAgBjE,KAAKI,QAAQ8D,YACjD5E,GAAG6E,KAAKC,UAAU,+BACjBC,MACC3B,GAAI1C,KAAKG,WACTmC,OAAQA,KAEPgC,KACF,SAAUC,GAETvE,KAAKwE,cACJ5D,KAAKZ,MACP,SAAUuE,KAGR3D,KAAKZ,QACPY,KAAKZ,KAAMsC,SAKjBtC,KAAKoC,gBAAgBqC,QAEtBD,WAAY,WAEXlF,GAAGyE,KAAKC,YAAYU,OAAO1E,KAAKI,SAEjCsB,2BAA4B,SAAUS,GAErCA,EAAMwC,kBACNxC,EAAMyC,iBACN5E,KAAK6E,cAAgB7E,KAAK8E,mBAAmB3C,GAC7C,GAAInC,KAAK6E,cACT,CACC7E,KAAK6E,cAAcJ,SAGrBK,mBAAoB,SAAU3C,GAE7B,IAAI4C,EAAQ/E,KAAKgF,mBAAmB7C,GAEpC,GAAI4C,EAAM3D,OAAS,EACnB,CACC,IAAIsB,EAAK,kBACT,IAAK,IAAIvB,EAAI,EAAGA,EAAI4D,EAAM3D,OAAQD,IAClC,CACCuB,EAAKA,EAAKqC,EAAM5D,GAAGuB,GAEpB,OAAOpD,GAAG2F,UAAUC,QACnBH,MAAOA,EACPI,UAAW,IACXzC,GAAIA,EACJ0C,YAAajD,EAAMI,cACnB8C,MAAO,KACPtC,WAAY,KACZJ,SAAU,OAGZ,OAAO,MAERqC,mBAAoB,SAAU7C,GAE7B,IAAIK,EAAUL,EAAMI,cAAcC,QAClC,IAAIuC,KACJ,GAAIvC,EAAQ8C,aAAe,IAC3B,CACCP,EAAMQ,MACL7C,GAAI,aAAepD,GAAGkG,KAAKC,gBAAgB,IAC3C9B,KAAMrE,GAAGkG,KAAKE,iBAAiBpG,GAAG+D,QAAQ,0CAC1CsC,QAAS,SAAUC,GAElB5F,KAAK6E,cAAcxC,QACnB/C,GAAG6E,KAAKC,UACP,4BAECC,KAAMrE,KAAK6F,2BAA2BD,KAEtCtB,KACD,SAAUsB,EAAMrB,GAEfvE,KAAK8F,0BAA0BvB,EAASF,KAAK0B,YAC5CnF,KAAKZ,KAAM4F,GACb,SAAUrB,KAER3D,KAAKZ,QACPY,KAAKZ,KAAMmC,EAAMI,iBAGrB,GAAIC,EAAQwD,UAAY,IACxB,CACCjB,EAAMQ,MACL7C,GAAI,UAAYpD,GAAGkG,KAAKC,gBAAgB,IACxC9B,KAAMrE,GAAGkG,KAAKE,iBAAiBpG,GAAG+D,QAAQ,uCAC1CsC,QAAS,SAAUM,GAElBjG,KAAK6E,cAAcxC,QACnBrC,KAAK2B,oBAAoBsE,IACxBrF,KAAKZ,KAAMmC,EAAMI,iBAIrB,OAAOwC,GAERc,2BAA4B,SAAUK,GAErC,IAAIC,EAAW,IAAIC,SACnB,IAAIC,EAASH,EAAY5E,iBAAiB,wBAC1C,IAAK,IAAIH,EAAI,EAAGA,EAAIkF,EAAOjF,OAAQD,IACnC,CACCgF,EAASG,OAAOD,EAAOlF,GAAGoF,KAAMF,EAAOlF,GAAGqF,OAE3CL,EAASG,OAAO,uBAAwBtG,KAAKiC,uBAAyB,IAAM,KAC5E,IAAIwE,EAAezG,KAAKwB,gBAAgB,UAAWlC,GAAGoH,WAAWR,GAAcS,IAAO,QACtF,GAAIF,GAAgBA,EAAajE,QACjC,CACC2D,EAASG,OAAO,mBAAoBG,EAAajE,QAAQoE,OAE1D,OAAOT,GAERxE,oBAAqB,SAAUQ,GAE9B,IAAI+D,EAAc/D,EAClB,GAAIA,EAAMwC,gBACV,CACCxC,EAAMwC,kBACNxC,EAAMyC,iBACNsB,EAAc/D,EAAMI,cAErB,GAAI2D,EAAYW,WAChB,CACC,OAEDX,EAAYW,WAAa,KACzBX,EAAYY,MAAMC,QAAU,EAC5B,IAAIZ,EAAWnG,KAAK6F,2BAA2BK,GAC/C5G,GAAG6E,KAAKC,UACP,yBAECC,KAAM8B,IAEN7B,KACD,SAAUC,GAET2B,EAAYY,MAAMC,QAAU,EAC5Bb,EAAYW,WAAa,MACzB7G,KAAKgH,wBAAwBzC,EAASF,KAAK0B,YAC1CnF,KAAKZ,MACP,SAAUuE,GAET2B,EAAYW,WAAa,MACzBX,EAAYY,MAAMC,QAAU,GAC3BnG,KAAKZ,QAET8F,0BAA2B,SAAUC,GAEpC/F,KAAKiH,uBAAuBlB,IAE7BiB,wBAAyB,SAAUjB,GAElC/F,KAAKiH,uBAAuBlB,IAE7BkB,uBAAwB,SAAUlB,GAEjC,IAAK/F,KAAKkH,oBACV,CACC,OAED,IAAI/E,EAAQ,IAAIgF,YAAY,qCAC3BlG,QACCmG,MAAOrB,EAAUsB,aAGnBrH,KAAKkH,oBAAoBI,cAAcnF,IAExC+E,kBAAmB,WAElB,OAAOrG,SAASC,cAAc,4CAnRhC","file":"table.map.js"}