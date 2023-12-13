const main = $("#main");
const mainStatus = $("#main-status");
const mainStatusTime = $("#main-status-time");
const mainStatusContent = $("#main-status-content");
const mainStatusLikeCounter = $("#main-status-like-counter");
const mainStatusHeart = $("#main-status-heart");
const ancestorStatusContainer = $("#ancestor-status-container");

const mainStatusData = mainStatus.data();

let statusUpdatedAt = mainStatusData.updatedAt == null ? null : formatStatusDate(parseMysqlDateTime(mainStatusData.updatedAt));
const statusCreatedAt = formatStatusDate(parseMysqlDateTime(mainStatusData.createdAt));

function updateMainStatusTime() {
  mainStatusTime.html(statusCreatedAt);

  if (statusUpdatedAt !== null) {
    mainStatusTime.append(`, Updated ${statusUpdatedAt}`);
  }
}

updateMainStatusTime();

const editStatusButton = $("#edit-status");
const editStatusContainer = $("#edit-status-container");
const editInput = $("#edit-input");
const editInputCounter = $("#edit-input-counter");
const postEditCancelButton = $("#post-edit-cancel");
const postEditSubmitButton = $("#post-edit-submit");

editInput.keyup(function () {
  const editLength = editInput.val().trim().length;
  editInputCounter.html(editLength);
  postEditSubmitButton.prop("disabled", editLength <= 0);
});

editStatusButton.click(function () {
  editStatusButton.blur();
  editInput.val(mainStatusContent.text());
  editInput.keyup();
  mainStatusContent.addClass("d-none");
  editStatusContainer.removeClass("d-none");
});

postEditCancelButton.click(function () {
  editInput.val("");
  mainStatusContent.removeClass("d-none");
  editStatusContainer.addClass("d-none");
});

postEditSubmitButton.click(function () {
  $.post("/api/edit_status", {
    status_id: mainStatusData.id,
    content: editInput.val().trim()
  }, function (data) {
    const { status_content, updated_at } = data;
    statusUpdatedAt = formatStatusDate(parseMysqlDateTime(updated_at));

    updateMainStatusTime();
    mainStatusContent.text(status_content);
    postEditCancelButton.click();
  });
});

const replyInput = $("#reply-input");
const replyInputCounter = $("#reply-input-counter");
const postReplyButton = $("#post-reply");

replyInput.keyup(function () {
  const replyLength = replyInput.val().trim().length;
  replyInputCounter.html(replyLength);
  postReplyButton.prop("disabled", replyLength <= 0);
});

postReplyButton.click(function () {
  $.post("/api/post_reply", {
    parent_status_id: mainStatusData.id,
    content: replyInput.val().trim()
  }, function (data) {
    const { status_id, status_content } = data;

    const statusDiv = createStatusDiv(data);
    statusContainer.prepend(statusDiv);
    setupStatusDiv(status_id, status_content);
    replyInput.val("");
    replyInput.keyup();

    if (earliestStatusId === 0) {
      earliestStatusId = status_id;
    }
  });
});

$.post("/api/get_status_ancestor", {
  id: mainStatusData.id
}, function (data) {
  const oldHeight = main.height();
  let ancestorHeight = 0;

  for (let i = 0; i < data.length; i++) {
    const { status_id, status_content } = data[i];
    const statusDiv = createStatusDiv(data[i]);

    ancestorStatusContainer.prepend(statusDiv);
    setupStatusDiv(status_id, status_content);

    ancestorHeight += $(`#status-${status_id}`).outerHeight();

    if (i < (data.length - 1)) {
      $(`#status-${status_id} #thread-line-before`).removeClass("c-hidden");
    }

    $(`#status-${status_id} #thread-line-after`).removeClass("c-hidden");
  }

  if (data.length > 0) {
    mainStatus.find("#thread-line-before").removeClass("c-hidden");
  }

  main.css("min-height", (oldHeight + ancestorHeight) + "px");
  mainStatus.get(0).scrollIntoView();
});

mainStatus.find(".c-status-like").click(function () {
  $.post("/api/like", {
    status_id: mainStatusData.id
  }, function (data) {
    const {
      liked,
      new_like_count
    } = data;

    if (new_like_count !== undefined) {
      mainStatusLikeCounter.html(new_like_count);
    }

    if (liked) mainStatusHeart
      .removeClass("fa-regular")
      .addClass("fa-solid");
    else mainStatusHeart
      .removeClass("fa-solid")
      .addClass("fa-regular");
  });
});

function fetchReply(idBefore) {
  $.get("/api/get_reply", {
    parent_status_id: mainStatusData.id,
    id_before: idBefore
  }, statusResponseHandler);
}

$("#delete-status-modal #confirm-button").click(function () {
  $.post("/api/delete_status", {
    status_id: mainStatusData.id
  }, function () {
    window.location.reload();
  });
});

fetchReply(0);

$(window).scroll(function () {
  if ($(window).scrollTop() + $(window).height() === $(document).height()) {
    fetchReply(earliestStatusId);
  }
});