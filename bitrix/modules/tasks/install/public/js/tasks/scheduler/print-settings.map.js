{"version":3,"sources":["print-settings.js"],"names":["BX","namespace","Scheduler","PrintSettings","timeline","this","printer","Printer","defaultFormat","init","prototype","popupWindow","layout","content","errorAlert","errorText","dateFrom","dateTo","format","orientation","border","fitToPage","show","getPopup","getPrinter","closePrintWindow","close","destroy","handlePrintButtonClick","setFormat","getFormat","setOrientation","getOrientation","setBorder","getBorder","setDateFrom","getDateFrom","setDateTo","getDateTo","setFitToPage","getFitToPage","print","isChrome","alert","PrintSettingsAlert","exception","showError","message","error","insertBefore","getErrorAlert","firstElementChild","innerHTML","hideError","remove","create","props","className","children","events","click","bind","test","navigator","userAgent","vendor","PopupWindow","autoHide","closeByEsc","titleBar","closeIcon","draggable","buttons","PopupWindowButton","text","PopupWindowButtonLink","onPopupClose","createRow","createField","getDateFromField","getDateToField","getFormatField","getOrientationField","getBorderField","getFitToPageField","setContent","caption","control","controls","Array","isArray","type","isNotEmptyString","concat","fields","attrs","readonly","value","formatDate","getViewportDateFrom","handleDateClick","getViewportDateTo","date","convertBitrixFormat","replace","event","calendar","node","currentTarget","field","bTime","bUseSecond","bSetFocus","Object","keys","getPaperSizes","map","size","selected","checked","parseDate","select","options","selectedIndex","printWindow","getPrintWindow","addEventListener","handleBeforePrint","handleAfterPrint","timer","setInterval","closed","clearInterval","self","overlay"],"mappings":"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","file":""}