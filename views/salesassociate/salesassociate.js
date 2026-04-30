//BELOW IS TO ADD MULTIPLE LINE ITEMS TO THE CREATE NEW QUOTE
const addServiceBtn = document.getElementById('addService');
const servicesContainer = document.getElementById('services');

addServiceBtn.addEventListener('click', () => {

  // Create all of the elements that are needed
  const serviceRow = document.createElement('div');
  const newServiceDescription = document.createElement('input');
  const newServicePrice = document.createElement('input');
  const lineBreak = document.createElement('br');
  const deleteServiceBtn = document.createElement('button');

  serviceRow.className = 'serviceRow';

  //Set the information for the service description textbox
  newServiceDescription.type = 'text';
  newServiceDescription.placeholder = 'Enter Service Description';
  newServiceDescription.name = 'services[]';
  newServiceDescription.required = true;

  //Set the information for the service price box
  newServicePrice.type = 'number';
  newServicePrice.step = '0.01';
  newServicePrice.placeholder = 'Enter Service Price';
  newServicePrice.name = 'prices[]';
  newServicePrice.required = true;

  //Create the button to delete the row
  deleteServiceBtn.innerHTML = '<i class="fa-solid fa-delete-left"></i>';
  deleteServiceBtn.className = 'deleteServiceBtn';
  deleteServiceBtn.type = 'button';

  //Create an event listener for the delete button that removes the row
  deleteServiceBtn.addEventListener('click', () => {
    serviceRow.remove();
  });

  //Append all elements to the serviceRow
  serviceRow.appendChild(newServiceDescription);
  serviceRow.appendChild(newServicePrice);
  serviceRow.appendChild(deleteServiceBtn);

  //Append the serviceRow to the container
  servicesContainer.appendChild(serviceRow);

});

// Attach variables to the elements on the page
const addNotesBtnCreate = document.getElementById('addNoteCreate');
const notesContainerCreate = document.getElementById('notesContainerCreate');

// Set an onclick event listener for the add notes button in the create new quote modal
addNotesBtnCreate.addEventListener('click', () => {

  //Create all the required elements
  const newNote = document.createElement('div');
  const newNoteBox = document.createElement('textarea');
  const deleteNoteButton = document.createElement('button');
  const privpubDropdown = document.createElement('select');
  const privOption = document.createElement('option');
  const pubOption = document.createElement('option');


  //Set the values for the dropdown. This is used to choose if a note is private or public
  privpubDropdown.className = 'form-select-sm';
  privpubDropdown.name = 'issecret[]';
  privOption.value = '1';
  privOption.textContent = 'Private';
  pubOption.value = '0';
  pubOption.textContent = 'Public';

  //Append the public and private options to the dropdown
  privpubDropdown.appendChild(privOption);
  privpubDropdown.appendChild(pubOption);



  newNote.className = 'newNote';

  //Create a delete button to delete the notes
  deleteNoteButton.innerHTML = '<i class="fa-solid fa-trash-can"></i>';
  deleteNoteButton.className = 'deleteServiceBtn';
  deleteNoteButton.type = 'button';

  //Set the information for the newNoteBox
  newNoteBox.type = 'text';
  newNoteBox.placeholder = 'Enter a new Note';
  newNoteBox.name = 'notes[]';
  newNoteBox.className = 'newNoteBox';
  newNoteBox.rows = '4';
  newNoteBox.cols = '50';

  //Create an onclick listener for the delete note button
  deleteNoteButton.addEventListener('click', () => {
    newNote.remove();
  });
  

  //Append all the elements to the newNote div
  newNote.appendChild(newNoteBox);
  newNote.appendChild(privpubDropdown);
  newNote.appendChild(deleteNoteButton);

  //Append the newNote div to the notes container
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
