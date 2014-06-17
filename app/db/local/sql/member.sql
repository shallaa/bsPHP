--list
select testmember_rowid,userid,nick from testmember order by testmember_rowid
--view
select*from testmember where testmember_rowid=@rowid:testmember.testmember_rowid@
--add
:insert testmember @id:userid@ @pass:pw@ @alias:nick@
--edit
:update testmember @id:userid@ @pass:pw@ @alias:nick@ where @rowid:testmember_rowid@
--del
:delete testmember where @rowid:testmember_rowid@