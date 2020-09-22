{"version":3,"sources":["shift-multiple.js"],"names":["BX","namespace","Timeman","Component","Schedule","ShiftEdit","Multiple","options","this","observersData","apply","arguments","workdaysOptions","shiftWorkdaysOptions","customWorkdaysText","shiftedScheduleTypeName","uniqueIndex","isScheduleFixed","visible","formFields","container","querySelectorAll","i","length","name","replace","showElement","undefined","shiftId","selectOneByRole","value","workdaysToggle","workdaysInput","workDaysSelector","pencil","nameInput","workdaysBlocks","selectAllByRole","nameBlock","workDaysBlock","nameSpan","deleteSelfBtn","defaultName","textContent","prevShiftEnd","workTimeStartLink","workTimeStartInput","prevShiftStart","prevDuration","calculateDurationSeconds","workTimeEndLink","beautifyTime","convertFormattedTimeToSecs","workTimeEndInput","id","querySelector","htmlFor","isScheduleShifted","breakTimeLink","initDurationWithoutBreak","addSelfEventHandlers","prototype","__proto__","constructor","bind","delegate","showWorkdaysOptionsPopup","startNameEdit","endNameEdit","onDeleteSelfBtnClick","processBeforeCollectFormData","innerText","workDaysItems","workdays","checked","push","sort","join","getFormattedDeltaTime","minuend","subtrahend","beautifyTimeLocal","onScheduleTypeSelected","selectedValue","fixedScheduleTypeName","hideElement","showWorkdaysBySelectedValue","event","focus","val","util","htmlspecialchars","attachOnDeleteEvent","obj","eventType","observer","document","updateOnShiftEvent","shift","remove","buildWorkdaysPopup","menuItems","item","text","title","dataset","onclick","workdaysPopup","close","PopupMenu","create","Math","random","autoHide","show"],"mappings":"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","file":"shift-multiple.map.js"}