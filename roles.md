**Role 1: Frontend Engineer (Sales Associate Interface)**
- Responsibilities:
  - Login page (/login)
  - Create/edit quote page
  - Add, line items, secret notes, customer email, finalize button
  - Use Cases: Create Quote, Finalize Quote

**Role 2: Backend Engineer (Quote Management System)**
- Responsibilities:
- API logic
- Status transitions: draft → finalized → sanctioned → ordered
- Pricing:
  - sum(line items)
  - apply discount
- Email sending (can fake this with console logs if needed)

**Role 3: Database Engineer (Data & Integration Layer)**
- Design application DB schema
- Maintain relationships:
  - quotes w/ line_items and notes
  - quotes w/ associates
- Optimize queries

**Role 4: Internal Systems Engineer (HQ + Orders)**
- Build HQ interface for quote review
- Edit line items and apply discounts
- Approve (sanction) quotes
- Convert quotes to purchase orders
- Integrate with external processing system
- Handle returned processing date and commission rate
- Calculate and store commissions
- Use Cases: Quote Review, Purchase Order Processing, Purchase Confirmation

**Role 5: Admin & QA**
- Build admin interface
- Add/edit/delete sales associates
- Search quotes by status, date, associate, and customer
- Perform end-to-end testing
- Test edge cases (discounts, missing data, etc.)
- Ensure emails exclude secret notes
