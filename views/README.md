# Overview
- UI templates live here!
- make views as we need them/start working
- referenced our use case model 

### General Interface Flow
- **Initial login portal**
  - Sales Associate types user, pass, then "login" button
    - i feel like we dont need to do any data validation here, just assume everyone who enters a user/pass is a sales associate
- **Landing Page for sales associate**
  - Use Case: Create Quote
  - Sales Associate creates/edits/finalizes existing quote
    - select customer, display info, add line items, add notes, "send email" button to customer, save draft
    - add button for "finalize" next to "save as draft"
      - Status will change, quote becomes uneditable, HQ portal receives this quote
  - Stimulus: User (sales associate) logs in, selects button to create/edit
  - quote is saved in DB as "draft"
  - Can also add a separate view for customer selection to make it all look nice
    - landing page shows list of quotes w/ an "edit" or "finalize" button, top of page can have "add quote" and "search customer"
    - "search customer" button can have like a popup search bar or we can add a whole separate page?
- **HQ Interface**
  - Use Case: Quote Review
  - HQ logs in?
  - HQ reviews finalized quotes, can modify details
    - view/edit line items, applies discount (as percent or fixed amount), add notes (secret or not secret)
    - mark quote as "sanctioned" or "unresolved"
  - Stimulus: HQ receives finalized quote
  - Updated quote stored in DB, price recalculated, status updated
- **HQ and External System**
  - Use Case: Purchase Order Processing
  - HQ converts sanctioned quote into an order
    - quote is "sent" to EPS --> a button can say "Confirm Purchase"
    - EPS will return a date, commission rate (we calculate the actual commission)
    - update the quote and associate info
  - Use Case: Purchase Confirmation
    - HQ selecting "Confirm Purchase" automatically sends email to customer
      - we dont do anything after that, just maybe display a message saying "confirmation email sent to customer [name]"
    - Stimulus: order processed, "Confirm Purchase" button clicked
- **Admin**
  - Use Case: Maintain Sales Associate and Quote Info
  - Admin logs on and can see a list of sales associates
  - Can click on associate record to see info, buttons to add/edit/delete associate
  - DB gets updated
  
 ### Databases
 - Legacy DB (provided) --> for validating customer info
 - System DB
   - contains quotes, line items, notes, sales associates
   - Would need to flesh out the schema, each of those tables/their attributes
