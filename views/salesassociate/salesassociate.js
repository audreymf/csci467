//BELOW IS TO ADD MULTIPLE LINE ITEMS TO THE CREATE NEW QUOTE
const addServiceBtn = document.getElementById('addService');
const servicesContainer = document.getElementById('services');

addServiceBtn.addEventListener('click', () => {

  const serviceRow = document.createElement('div');
  const newServiceDescription = document.createElement('input');
  const newServicePrice = document.createElement('input');
  const lineBreak = document.createElement('br');
  const deleteServiceBtn = document.createElement('button');

  serviceRow.className = 'serviceRow';

  newServiceDescription.type = 'text';
  newServiceDescription.placeholder = 'Enter Service Description';
  newServiceDescription.name = 'services[]';
  newServiceDescription.required = true;

  newServicePrice.type = 'number';
  newServicePrice.step = '0.01';
  newServicePrice.placeholder = 'Enter Service Price';
  newServicePrice.name = 'prices[]';
  newServicePrice.required = true;

  deleteServiceBtn.innerHTML = '<i class="fa-solid fa-delete-left"></i>';
  deleteServiceBtn.className = 'deleteServiceBtn';
  deleteServiceBtn.type = 'button';

  deleteServiceBtn.addEventListener('click', () => {
    serviceRow.remove();
  });

  serviceRow.appendChild(newServiceDescription);
  serviceRow.appendChild(newServicePrice);
  serviceRow.appendChild(deleteServiceBtn);

  servicesContainer.appendChild(serviceRow);

});

const addNotesBtnCreate = document.getElementById('addNoteCreate');
const notesContainerCreate = document.getElementById('notesContainerCreate');

addNotesBtnCreate.addEventListener('click', () => {

  const newNote = document.createElement('div');
  const newNoteBox = document.createElement('textarea');
  const deleteNoteButton = document.createElement('button');
  const privpubDropdown = document.createElement('select');
  const privOption = document.createElement('option');
  const pubOption = document.createElement('option');


  privpubDropdown.className = 'form-select-sm';
  privpubDropdown.name = 'issecret[]';
  privOption.value = '1';
  privOption.textContent = 'Private';
  pubOption.value = '0';
  pubOption.textContent = 'Public';

  privpubDropdown.appendChild(privOption);
  privpubDropdown.appendChild(pubOption);



  newNote.className = 'newNote';

  deleteNoteButton.innerHTML = '<i class="fa-solid fa-trash-can"></i>';
  deleteNoteButton.className = 'deleteServiceBtn';
  deleteNoteButton.type = 'button';

  newNoteBox.type = 'text';
  newNoteBox.placeholder = 'Enter a new Note';
  newNoteBox.name = 'notes[]';
  newNoteBox.className = 'newNoteBox';
  newNoteBox.rows = '4';
  newNoteBox.cols = '50';

  deleteNoteButton.addEventListener('click', () => {
    newNote.remove();
  });
  

  newNote.appendChild(newNoteBox);
  newNote.appendChild(privpubDropdown);
  newNote.appendChild(deleteNoteButton);

  notesContainerCreate.appendChild(newNote);


});

// BELOW IS TO ADD LINE ITEMS AND NOTES TO THE EDIT QUOTE MODAL

const addNotesBtnEdit = document.getElementById('addNoteEdit');
const notesContainer = document.getElementById('notesContainerEdit');

addNotesBtnEdit.addEventListener('click', () => {

  const newNote = document.createElement('div');
  const newNoteBox = document.createElement('textarea');
  const deleteNoteButton = document.createElement('button');
  const privpubDropdown = document.createElement('select');
  const privOption = document.createElement('option');
  const pubOption = document.createElement('option');

  privpubDropdown.className = 'form-select-sm';
  privpubDropdown.name = 'issecret[]';
  privOption.value = '1';
  privOption.textContent = 'Private';
  pubOption.value = '0';
  pubOption.textContent = 'Public';

  privpubDropdown.appendChild(privOption);
  privpubDropdown.appendChild(pubOption);



  newNote.className = 'newNote';

  deleteNoteButton.innerHTML = '<i class="fa-solid fa-trash-can"></i>';
  deleteNoteButton.className = 'deleteServiceBtn';
  deleteNoteButton.type = 'button';

  newNoteBox.type = 'text';
  newNoteBox.placeholder = 'Enter a new Note';
  newNoteBox.name = 'notes[]';
  newNoteBox.className = 'newNoteBox';
  newNoteBox.rows = '4';
  newNoteBox.cols = '50';

  deleteNoteButton.addEventListener('click', () => {
    newNote.remove();
  });

  

  newNote.appendChild(newNoteBox);
  newNote.appendChild(privpubDropdown);
  newNote.appendChild(deleteNoteButton);

  notesContainer.appendChild(newNote);


});

const addServiceBtnEdit = document.getElementById('addServiceEdit');
const servicesContainerEdit = document.getElementById('editServices');

addServiceBtnEdit.addEventListener('click', () => {

  const serviceRow = document.createElement('div');
  const newServiceDescription = document.createElement('input');
  const newServicePrice = document.createElement('input');
  const lineBreak = document.createElement('br');
  const deleteServiceBtn = document.createElement('button');

  serviceRow.className = 'serviceRow';

  newServiceDescription.type = 'text';
  newServiceDescription.placeholder = 'Enter Service Description';
  newServiceDescription.name = 'services[]';
  newServiceDescription.required = true;

  newServicePrice.type = 'number';
  newServicePrice.step = '0.01';
  newServicePrice.placeholder = 'Enter Service Price';
  newServicePrice.name = 'prices[]';
  newServicePrice.required = true;

  deleteServiceBtn.innerHTML = '<i class="fa-solid fa-delete-left"></i>';
  deleteServiceBtn.className = 'deleteServiceBtn';
  deleteServiceBtn.type = 'button';

  deleteServiceBtn.addEventListener('click', () => {
    serviceRow.remove();
  });

  serviceRow.appendChild(newServiceDescription);
  serviceRow.appendChild(newServicePrice);
  serviceRow.appendChild(deleteServiceBtn);

  servicesContainerEdit.appendChild(serviceRow);

});
