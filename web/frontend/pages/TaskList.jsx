import { Card, Page, Text, List, BlockStack } from "@shopify/polaris";
import { TitleBar, useAuthenticatedFetch } from "@shopify/app-bridge-react";
import { useTranslation } from "react-i18next";
import TaskItem from "../components/TaskList/Index";
import { useEffect, useState } from "react";

export default function TaskList() {
  const { t } = useTranslation();
  const fetch = useAuthenticatedFetch();
  const [tasks,setTasks] = useState([]);

  useEffect(() => {

    const response = fetch("/api/tasks", {
        method: "GET",
        headers: {
            "Content-Type": "application/json",
        }
    })
      .then((response) => {
          if (!response.ok) {
              return "not ok";
          }
          return response.json();

      }).then((response) => {
        if(response.success){
          setTasks(response.data?.data)
        }
      })
      .catch((error) => {
          console.log(error);
      });
  },[]);

  let taskList = [
    {
      "id": 1,
      "title": "Auto-tag products based on inventory level",
      "description": "This task tags products based on their inventory level, ensuring that out-of-stock products are labeled for restocking.",
      "tags": ["Inventory", "Product", "Tag"]
    },
    {
      "id": 2,
      "title": "Automatically adjust prices during promotions",
      "description": "Automatically applies discount pricing to selected products during promotion periods.",
      "tags": ["Price", "Promotion", "Discount"]
    },
    {
      "id": 3,
      "title": "Sync product inventory with warehouse",
      "description": "Synchronizes product inventory with your warehouse management system to ensure up-to-date stock levels.",
      "tags": ["Inventory", "Sync", "Warehouse"]
    },
    {
      "id": 4,
      "title": "Auto-tag low-stock products for urgent restocking",
      "description": "Tags products that have reached a low stock threshold, helping you prioritize restocking.",
      "tags": ["Inventory", "Low Stock", "Tag"]
    },
    {
      "id": 5,
      "title": "Automatically update product prices based on cost changes",
      "description": "Automatically adjusts product prices when there are changes in supplier costs to maintain profitability.",
      "tags": ["Price", "Cost Change", "Automation"]
    },
    {
      "id": 6,
      "title": "Auto-apply sales tax to products",
      "description": "Automatically applies the correct sales tax rates to products based on the customer's location.",
      "tags": ["Tax", "Product", "Location"]
    },
    {
      "id": 7,
      "title": "Auto-tag customers with high purchase frequency",
      "description": "Tags customers who have made frequent purchases, allowing you to target them with special offers.",
      "tags": ["Customer", "Frequency", "Tag"]
    },
    {
      "id": 8,
      "title": "Automate product removal when discontinued",
      "description": "Automatically removes discontinued products from your online store to keep the catalog up-to-date.",
      "tags": ["Discontinued", "Product", "Automation"]
    },
    {
      "id": 9,
      "title": "Automate restocking reminders for high-demand products",
      "description": "Automatically generates restocking reminders for high-demand products to ensure they stay in stock.",
      "tags": ["Restocking", "High Demand", "Reminder"]
    },
    {
      "id": 10,
      "title": "Automatically tag discounted products",
      "description": "Tags products that have a discount applied, making them easy to locate and manage.",
      "tags": ["Discount", "Product", "Tag"]
    }
  ];
  
  return (
    <Page>
      <TitleBar
        title={t("taskList.pageName")}
        primaryAction={{
          content: t("PageName.primaryAction"),
          onAction: () => console.log("Primary action"),
        }}
        secondaryActions={[
          {
            content: t("PageName.secondaryAction"),
            onAction: () => console.log("Secondary action"),
          },
        ]}
      />  
        <BlockStack gap={200}>
        {
            tasks.map(taskItem => {
                return <TaskItem taskItem={taskItem}/>
            })
        }
      </BlockStack>
    </Page>
  );
}